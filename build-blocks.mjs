/**
 * Builds each block's editor script into its own `blocks/<name>/build/` directory.
 *
 * `wp-scripts build a/src/index.js b/src/index.js` names every entry "index" and
 * writes them all to a single root `build/index.js`, so the blocks overwrite each
 * other and none lands where its block.json points (`file:./build/index.js`,
 * resolved relative to the block). Building one block per invocation with an
 * explicit --output-path is what actually produces a loadable editor script.
 *
 * Usage: node build-blocks.mjs [--watch]
 */
import { readdirSync, existsSync } from 'node:fs';
import { spawnSync } from 'node:child_process';
import { join } from 'node:path';

const BLOCKS_DIR = 'blocks';
const watch = process.argv.includes('--watch');

const blocks = readdirSync(BLOCKS_DIR, { withFileTypes: true })
    .filter((entry) => entry.isDirectory())
    .map((entry) => entry.name)
    .filter((name) => existsSync(join(BLOCKS_DIR, name, 'src', 'index.js')));

if (!blocks.length) {
    console.error('No blocks with a src/index.js found.');
    process.exit(1);
}

if (watch && blocks.length > 1) {
    // `wp-scripts start` blocks on the first invocation, so a sequential loop
    // would only ever watch one block.
    console.error(
        'Watch mode builds a single block at a time. Run:\n' +
        blocks.map((b) => `  npx wp-scripts start ${BLOCKS_DIR}/${b}/src/index.js --output-path=${BLOCKS_DIR}/${b}/build`).join('\n')
    );
    process.exit(1);
}

for (const block of blocks) {
    console.log(`\n▸ Building ${block}`);
    const result = spawnSync(
        'npx',
        [
            'wp-scripts',
            'build',
            `${BLOCKS_DIR}/${block}/src/index.js`,
            `--output-path=${BLOCKS_DIR}/${block}/build`,
        ],
        { stdio: 'inherit', shell: true }
    );

    if (result.status !== 0) {
        console.error(`\nFailed to build ${block}.`);
        process.exit(result.status ?? 1);
    }
}

console.log(`\nBuilt ${blocks.length} block(s).`);
