/**
 * Builds the distributable plugin zip.
 *
 *   node build-release.mjs        -> dist/bailaya-<version>.zip
 *
 * The zip contains a single top-level `bailaya/` folder (the plugin slug), which
 * is what WordPress expects when installing from a file.
 *
 * Two things have to happen here that a plain `git archive` would get wrong:
 *
 *   - `vendor/` is gitignored, but the plugin does not run without it (it needs
 *     bailaya/core, Guzzle and phpdotenv). Composer's production autoloader is
 *     rebuilt before staging.
 *   - `blocks/<name>/build/index.js` is gitignored too, but each block.json
 *     points at it as the editor script. Without it the blocks cannot be
 *     configured in the editor.
 *
 * Everything is staged through an explicit allowlist rather than a denylist, so
 * a new dev file at the repo root can never leak into a release by accident.
 */
import { execFileSync } from "node:child_process";
import {
  cpSync,
  existsSync,
  mkdirSync,
  readFileSync,
  readdirSync,
  rmSync,
  statSync,
} from "node:fs";
import path from "node:path";

const ROOT = import.meta.dirname;
const SLUG = "bailaya";
const DIST = path.join(ROOT, "dist");
const STAGE = path.join(DIST, SLUG);

/** Files and directories that ship. Anything not listed here is left behind. */
const INCLUDE = [
  "bailaya.php",
  "readme.txt",
  "LICENSE.txt",
  "composer.json",
  "includes",
  "templates",
  "assets",
  "languages",
  "blocks",
  "vendor",
];

/** Pruned from the staged tree after copying (dev leftovers inside kept dirs). */
const PRUNE = [
  // The .po/.pot are the sources; WordPress only reads the compiled .mo. They
  // are small and aid translators, so they stay — but nothing else does.
  /[\\/]node_modules[\\/]/,
  /[\\/]\.git/,
  /[\\/]\.DS_Store$/,
];

function run(cmd, args, label) {
  process.stdout.write(`  ${label}… `);
  try {
    execFileSync(cmd, args, { cwd: ROOT, stdio: "pipe", shell: true });
    console.log("ok");
  } catch (err) {
    console.log("FAILED");
    console.error(err.stdout?.toString() || err.message);
    process.exit(1);
  }
}

// ─── 1. Version must agree everywhere ────────────────────────────────────────
const pluginSrc = readFileSync(path.join(ROOT, "bailaya.php"), "utf8");
const readme = readFileSync(path.join(ROOT, "readme.txt"), "utf8");
const composer = JSON.parse(readFileSync(path.join(ROOT, "composer.json"), "utf8"));

const headerVersion = pluginSrc.match(/\*\s*Version:\s*([\d.]+)/)?.[1];
const constVersion = pluginSrc.match(/BAILAYA_WP_VER',\s*'([\d.]+)'/)?.[1];
const stableTag = readme.match(/^Stable tag:\s*([\d.]+)/m)?.[1];

const versions = {
  "plugin header": headerVersion,
  BAILAYA_WP_VER: constVersion,
  "readme Stable tag": stableTag,
  "composer.json": composer.version,
};

console.log("Version check");
const distinct = new Set(Object.values(versions));
for (const [where, v] of Object.entries(versions)) {
  console.log(`  ${where.padEnd(20)} ${v ?? "MISSING"}`);
}
if (distinct.size !== 1 || distinct.has(undefined)) {
  console.error("\nVersions disagree — refusing to build a release.");
  process.exit(1);
}
const VERSION = headerVersion;

// ─── 2. Build the things git does not track ──────────────────────────────────
console.log("\nBuilding");
run("composer", ["install", "--no-dev", "--optimize-autoloader", "--no-interaction"], "composer install --no-dev");
run("node", ["build-blocks.mjs"], "block editor scripts");

// Compile the translation catalogues so the shipped .mo always matches the .po.
for (const po of readdirSync(path.join(ROOT, "languages")).filter((f) => f.endsWith(".po"))) {
  const base = po.replace(/\.po$/, "");
  run(
    "msgfmt",
    ["--check", "-o", `languages/${base}.mo`, `languages/${po}`],
    `msgfmt ${base}`
  );
}

// ─── 3. Boot the plugin and fire its hooks ───────────────────────────────────
// `php -l` cannot see a missing `use` import, and neither can simply requiring
// the plugin — the work happens inside closures on plugins_loaded/init. Running
// them here is what catches "Class X not found" before a user does.
console.log("\nSmoke test");
try {
  const out = execFileSync("php", ["tools/smoke-test.php"], { cwd: ROOT, stdio: "pipe" })
    .toString()
    .trim();
  for (const line of out.split("\n")) console.log(`  ${line.trim()}`);
} catch (err) {
  console.error("  the plugin does not boot:\n");
  console.error((err.stdout?.toString() || err.message).replace(/^/gm, "  "));
  process.exit(1);
}

// ─── 4. Stage ────────────────────────────────────────────────────────────────
console.log("\nStaging");
rmSync(DIST, { recursive: true, force: true });
mkdirSync(STAGE, { recursive: true });

for (const entry of INCLUDE) {
  const from = path.join(ROOT, entry);
  if (!existsSync(from)) {
    console.error(`  missing: ${entry}`);
    process.exit(1);
  }
  cpSync(from, path.join(STAGE, entry), {
    recursive: true,
    filter: (src) => !PRUNE.some((re) => re.test(src)),
  });
  console.log(`  + ${entry}`);
}

// ─── 4. Sanity-check the staged tree ─────────────────────────────────────────
console.log("\nChecks");
const problems = [];

const mustExist = [
  "bailaya.php",
  "readme.txt",
  "LICENSE.txt",
  "vendor/autoload.php",
  "vendor/bailaya/core/src/Client.php",
];
for (const f of mustExist) {
  if (!existsSync(path.join(STAGE, f))) problems.push(`missing ${f}`);
}

// Every block needs its compiled editor script, or it cannot be configured.
for (const block of readdirSync(path.join(STAGE, "blocks"))) {
  const built = path.join(STAGE, "blocks", block, "build", "index.js");
  if (!existsSync(built)) problems.push(`block "${block}" has no build/index.js`);
}

// Every declared locale needs a compiled catalogue.
for (const loc of ["es_ES", "fr_FR", "de_DE", "ru_RU", "ka_GE"]) {
  if (!existsSync(path.join(STAGE, "languages", `bailaya-${loc}.mo`))) {
    problems.push(`missing compiled translation for ${loc}`);
  }
}

// Nothing developer-only may have slipped in.
const walk = (dir) =>
  readdirSync(dir).flatMap((e) => {
    const p = path.join(dir, e);
    return statSync(p).isDirectory() ? walk(p) : [p];
  });
const staged = walk(STAGE);
for (const f of staged) {
  const rel = path.relative(STAGE, f);

  // These must not appear anywhere, including inside dependencies.
  if (/(^|[\\/])node_modules[\\/]|(^|[\\/])\.git([\\/]|$)/.test(rel)) {
    problems.push(`must not ship: ${rel}`);
    continue;
  }

  // Our own dev files. Third-party packages under vendor/ carry their own
  // lockfiles and editor configs; those are part of the dependency, not a leak
  // from this repo, and WordPress does not care about them.
  if (!rel.startsWith("vendor") && /package(-lock)?\.json|\.idea|build-.*\.mjs/.test(rel)) {
    problems.push(`developer file leaked: ${rel}`);
  }
}

if (problems.length) {
  console.error("\nRelease is not shippable:");
  for (const p of problems) console.error(`  ✗ ${p}`);
  process.exit(1);
}
console.log(`  ✓ ${staged.length} files staged`);

// Boot the *staged* copy, not just the repo — this is the tree that ships, with
// its own bundled vendor/. If the production autoloader is missing a class, it
// fails here rather than on someone's site.
try {
  execFileSync("php", ["tools/smoke-test.php", STAGE], { cwd: ROOT, stdio: "pipe" });
  console.log("  ✓ staged plugin boots against its own bundled vendor/");
} catch (err) {
  console.error("\nThe staged plugin does not boot:");
  console.error((err.stdout?.toString() || err.message).replace(/^/gm, "  "));
  process.exit(1);
}

// ─── 5. Zip ──────────────────────────────────────────────────────────────────
const zipPath = path.join(DIST, `${SLUG}-${VERSION}.zip`);
console.log("\nPackaging");

// NOT PowerShell's Compress-Archive: on Windows PowerShell 5.1 it writes entry
// names with backslashes, which violates the ZIP spec (APPNOTE 4.4.17: paths use
// forward slashes). WordPress and any Linux unzip then either mangle the paths
// or refuse the archive. Python's zipfile normalises separators properly.
const zipScript = `
import os, zipfile
stage = r"${STAGE}"
root = os.path.dirname(stage)
with zipfile.ZipFile(r"${zipPath}", "w", zipfile.ZIP_DEFLATED) as z:
    for dirpath, _dirs, files in os.walk(stage):
        for name in files:
            full = os.path.join(dirpath, name)
            arc = os.path.relpath(full, root).replace(os.sep, "/")
            z.write(full, arc)
`;
execFileSync("python3", ["-c", zipScript], { stdio: "pipe" });

// Read the archive back and check it is actually well-formed and complete —
// a zip that builds is not the same as a zip WordPress can install.
const verifyScript = `
import sys, zipfile
z = zipfile.ZipFile(r"${zipPath}")
bad = z.testzip()
if bad:
    print("CORRUPT:", bad); sys.exit(1)
names = z.namelist()
problems = []
if any("\\\\" in n for n in names):
    problems.append("entry names contain backslashes (not a valid zip)")
if not all(n.startswith("${SLUG}/") for n in names):
    problems.append("entries are not under a single ${SLUG}/ folder")
for required in ["${SLUG}/bailaya.php", "${SLUG}/readme.txt", "${SLUG}/vendor/autoload.php"]:
    if required not in names:
        problems.append("missing " + required)
if problems:
    for p in problems: print("PROBLEM:", p)
    sys.exit(1)
print(len(names))
`;
let entryCount;
try {
  entryCount = execFileSync("python3", ["-c", verifyScript], { stdio: "pipe" })
    .toString()
    .trim();
} catch (err) {
  console.error("\nThe archive did not verify:");
  console.error(err.stdout?.toString() || err.message);
  process.exit(1);
}

const kb = Math.round(statSync(zipPath).size / 1024);
console.log(`  ${path.relative(ROOT, zipPath)}  (${kb} KB, ${entryCount} entries, verified)`);
console.log(`\nReady: install this zip via Plugins → Add New → Upload Plugin.`);
