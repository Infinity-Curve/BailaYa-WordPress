<?php
declare(strict_types=1);

namespace BailaYaWP\Admin;

use BailaYa\Client;
use BailaYaWP\ClientFactory;
use BailaYaWP\Helpers;

if (!defined('ABSPATH')) exit;

/**
 * Generic wp-admin screens for the Management API, driven by {@see Resources}.
 *
 * One list/create/edit/delete screen serves every resource; the descriptors say
 * which columns to show, which fields to collect, and which client methods to
 * call. Requires an API key or access token in Settings → BailaYa.
 */
final class ManagementPage
{
    private const CAPABILITY = 'manage_options';
    private const PER_PAGE = 20;

    public function register(): void
    {
        add_action('admin_menu', [$this, 'registerMenu']);
        add_action('admin_enqueue_scripts', [$this, 'enqueueAssets']);
        add_action('admin_post_bailaya_save', [$this, 'handleSave']);
        add_action('admin_post_bailaya_delete', [$this, 'handleDelete']);
    }

    /** Loads the delete-confirmation handler, on our screens only. */
    public function enqueueAssets(string $hook): void
    {
        if (!str_contains($hook, 'bailaya-')) {
            return;
        }

        wp_enqueue_script(
            'bailaya-admin',
            plugins_url('assets/js/admin.js', BAILAYA_WP_PATH . 'bailaya.php'),
            [],
            BAILAYA_WP_VER,
            true
        );
    }

    public function registerMenu(): void
    {
        add_menu_page(
            'BailaYa',
            'BailaYa',
            self::CAPABILITY,
            'bailaya-classes',
            fn() => $this->render('classes'),
            'dashicons-groups',
            58
        );

        foreach (Resources::all() as $key => $resource) {
            add_submenu_page(
                'bailaya-classes',
                (string)$resource['label'],
                (string)$resource['label'],
                self::CAPABILITY,
                'bailaya-' . $key,
                fn() => $this->render($key)
            );
        }
    }

    /** True when the plugin has credentials for the Management API. */
    private function hasCredentials(): bool
    {
        return (string)Helpers::get_option('api_key', '') !== ''
            || (string)Helpers::get_option('access_token', '') !== '';
    }

    // ─── Rendering ─────────────────────────────────────────────────────────────

    private function render(string $key): void
    {
        if (!current_user_can(self::CAPABILITY)) {
            wp_die(esc_html__('You do not have permission to manage BailaYa data.', 'bailaya'));
        }

        $resource = Resources::get($key);
        if (!$resource) {
            wp_die(esc_html__('Unknown BailaYa resource.', 'bailaya'));
        }

        echo '<div class="wrap">';

        if (!$this->hasCredentials()) {
            printf(
                '<h1>%s</h1><div class="notice notice-warning"><p>%s</p></div></div>',
                esc_html((string)$resource['label']),
                sprintf(
                    /* translators: %s: link to the settings page */
                    esc_html__('Add an API key or access token in %s to manage this data.', 'bailaya'),
                    '<a href="' . esc_url(admin_url('options-general.php?page=bailaya-settings')) . '">'
                        . esc_html__('Settings → BailaYa', 'bailaya') . '</a>'
                )
            );
            return;
        }

        $this->renderNotice();

        // Screen routing only — which view to draw. The capability check above gates
        // access; the state-changing handlers verify their own nonces.
        // phpcs:disable WordPress.Security.NonceVerification.Recommended
        $action = isset($_GET['action']) ? sanitize_key((string)$_GET['action']) : 'list';
        $id     = isset($_GET['id']) ? sanitize_text_field(wp_unslash((string)$_GET['id'])) : '';
        // phpcs:enable WordPress.Security.NonceVerification.Recommended

        if ($action === 'new' || ($action === 'edit' && $id !== '')) {
            $this->renderForm($key, $resource, $action === 'edit' ? $id : null);
        } else {
            $this->renderList($key, $resource);
        }

        echo '</div>';
    }

    /** Shows the outcome of the last save/delete, passed back via the redirect. */
    private function renderNotice(): void
    {
        // Display-only: these come back from our own post-save/delete redirect and are
        // escaped before output. No mutation happens here, so there is no nonce to check.
        // phpcs:disable WordPress.Security.NonceVerification.Recommended
        $message = isset($_GET['bailaya_message']) ? sanitize_text_field(wp_unslash((string)$_GET['bailaya_message'])) : '';
        if ($message === '') {
            return;
        }

        $isError = isset($_GET['bailaya_status']) && $_GET['bailaya_status'] === 'error';
        // phpcs:enable WordPress.Security.NonceVerification.Recommended
        printf(
            '<div class="notice notice-%s is-dismissible"><p>%s</p></div>',
            $isError ? 'error' : 'success',
            esc_html($message)
        );
    }

    /** @param array<string,mixed> $resource */
    private function renderList(string $key, array $resource): void
    {
        // Read-only pagination for the list view; nothing is mutated here, so there
        // is no form submission to carry a nonce. Saves and deletes are nonce-checked
        // in handleSave()/handleDelete().
        // phpcs:ignore WordPress.Security.NonceVerification.Recommended
        $paged  = isset($_GET['paged']) ? max(1, absint(wp_unslash($_GET['paged']))) : 1;
        $offset = ($paged - 1) * self::PER_PAGE;

        $paginated = $resource['paginated'] ?? true;
        $params    = $paginated ? ['limit' => self::PER_PAGE, 'offset' => $offset] : [];

        try {
            $rows = ($resource['list'])(ClientFactory::make(), $params);
        } catch (\Throwable $e) {
            printf(
                '<h1>%s</h1><div class="notice notice-error"><p>%s</p></div>',
                esc_html((string)$resource['label']),
                esc_html($e->getMessage())
            );
            return;
        }

        $newUrl = admin_url('admin.php?page=bailaya-' . $key . '&action=new');
        printf(
            '<h1 class="wp-heading-inline">%s</h1> <a href="%s" class="page-title-action">%s</a><hr class="wp-header-end">',
            esc_html((string)$resource['label']),
            esc_url($newUrl),
            esc_html(sprintf(
                /* translators: %s: resource name, e.g. "class" or "instructor" */
                __('Add %s', 'bailaya'),
                strtolower((string)$resource['singular'])
            ))
        );

        echo '<table class="wp-list-table widefat fixed striped"><thead><tr>';
        foreach ($resource['columns'] as $label) {
            echo '<th>' . esc_html((string)$label) . '</th>';
        }
        echo '<th>' . esc_html__('Actions', 'bailaya') . '</th></tr></thead><tbody>';

        if (!$rows) {
            printf(
                '<tr><td colspan="%d">%s</td></tr>',
                count($resource['columns']) + 1,
                esc_html__('Nothing here yet.', 'bailaya')
            );
        }

        foreach ($rows as $row) {
            echo '<tr>';
            foreach (array_keys($resource['columns']) as $prop) {
                echo '<td>' . esc_html($this->displayValue($row, (string)$prop)) . '</td>';
            }
            echo '<td>' . wp_kses(
                $this->rowActions($key, $resource, (string)$row->id),
                Helpers::allowed_admin_html()
            ) . '</td>';
            echo '</tr>';
        }

        echo '</tbody></table>';

        if ($paginated) {
            $this->renderPagination($key, $paged, count($rows));
        }
    }

    /**
     * Renders the edit/delete links for one row. Delete goes through admin-post
     * with a nonce.
     *
     * @param array<string,mixed> $resource
     */
    private function rowActions(string $key, array $resource, string $id): string
    {
        $links = [];

        if (isset($resource['update'])) {
            $editUrl = admin_url('admin.php?page=bailaya-' . $key . '&action=edit&id=' . rawurlencode($id));
            $links[] = '<a href="' . esc_url($editUrl) . '">' . esc_html__('Edit', 'bailaya') . '</a>';
        }

        if (isset($resource['delete'])) {
            $deleteUrl = wp_nonce_url(
                admin_url('admin-post.php?action=bailaya_delete&resource=' . $key . '&id=' . rawurlencode($id)),
                'bailaya_delete_' . $key . '_' . $id
            );
            // The prompt is bound in assets/js/admin.js: this markup goes through
            // wp_kses(), which strips inline handlers such as onclick.
            $links[] = '<a href="' . esc_url($deleteUrl) . '" class="submitdelete" data-confirm="'
                . esc_attr__('Delete this item? This cannot be undone.', 'bailaya')
                . '">' . esc_html__('Delete', 'bailaya') . '</a>';
        }

        return implode(' | ', $links);
    }

    private function renderPagination(string $key, int $paged, int $rowCount): void
    {
        $base = admin_url('admin.php?page=bailaya-' . $key);
        echo '<div class="tablenav"><div class="tablenav-pages">';

        if ($paged > 1) {
            printf(
                '<a class="button" href="%s">%s</a> ',
                esc_url($base . '&paged=' . ($paged - 1)),
                esc_html__('‹ Previous', 'bailaya')
            );
        }

        // The API returns no total, so a full page implies there may be another.
        if ($rowCount === self::PER_PAGE) {
            printf(
                '<a class="button" href="%s">%s</a>',
                esc_url($base . '&paged=' . ($paged + 1)),
                esc_html__('Next ›', 'bailaya')
            );
        }

        echo '</div></div>';
    }

    /** Renders one cell, flattening the values the DTOs can hold. */
    private function displayValue(object $row, string $prop): string
    {
        $value = $row->{$prop} ?? null;

        if ($value === null || $value === '') return '—';
        if (is_bool($value)) return $value ? __('Yes', 'bailaya') : __('No', 'bailaya');
        if ($value instanceof \DateTimeInterface) return $value->format('Y-m-d');
        if (is_array($value)) return implode(', ', array_map('strval', $value));
        if (is_object($value)) return (string)($value->name ?? '—');

        return (string)$value;
    }

    // ─── Form ──────────────────────────────────────────────────────────────────

    /** @param array<string,mixed> $resource */
    private function renderForm(string $key, array $resource, ?string $id): void
    {
        $isEdit = $id !== null;
        $existing = null;

        if ($isEdit) {
            try {
                $existing = ($resource['read'])(ClientFactory::make(), $id);
            } catch (\Throwable $e) {
                printf('<div class="notice notice-error"><p>%s</p></div>', esc_html($e->getMessage()));
                return;
            }
        }

        printf(
            '<h1>%s</h1>',
            esc_html(sprintf(
                $isEdit
                    /* translators: %s: resource name, e.g. "class" or "instructor" */
                    ? __('Edit %s', 'bailaya')
                    /* translators: %s: resource name, e.g. "class" or "instructor" */
                    : __('Add %s', 'bailaya'),
                strtolower((string)$resource['singular'])
            ))
        );

        echo '<form method="post" action="' . esc_url(admin_url('admin-post.php')) . '">';
        wp_nonce_field('bailaya_save_' . $key);
        echo '<input type="hidden" name="action" value="bailaya_save" />';
        echo '<input type="hidden" name="resource" value="' . esc_attr($key) . '" />';
        if ($isEdit) {
            echo '<input type="hidden" name="id" value="' . esc_attr($id) . '" />';
        }

        echo '<table class="form-table" role="presentation"><tbody>';

        foreach ($resource['fields'] as $field) {
            // The API rejects some fields on update, and others only exist there.
            if ($isEdit && ($field['create_only'] ?? false)) continue;
            if (!$isEdit && ($field['edit_only'] ?? false)) continue;

            $this->renderField($field, $existing);
        }

        echo '</tbody></table>';

        submit_button($isEdit ? __('Save changes', 'bailaya') : __('Create', 'bailaya'));

        printf(
            '<a href="%s" class="button button-secondary">%s</a>',
            esc_url(admin_url('admin.php?page=bailaya-' . $key)),
            esc_html__('Cancel', 'bailaya')
        );

        echo '</form>';
    }

    /** @param array<string,mixed> $field */
    private function renderField(array $field, ?object $existing): void
    {
        $name     = (string)$field['name'];
        $type     = (string)($field['type'] ?? 'text');
        $required = (bool)($field['required'] ?? false);
        $value    = $existing !== null ? ($existing->{$name} ?? null) : null;

        if ($value instanceof \DateTimeInterface) {
            $value = $value->format('Y-m-d');
        }

        echo '<tr><th scope="row"><label for="bailaya-' . esc_attr($name) . '">'
            . esc_html((string)$field['label'])
            . ($required ? ' <span class="description">' . esc_html__('(required)', 'bailaya') . '</span>' : '')
            . '</label></th><td>';

        $id  = 'bailaya-' . $name;
        $req = $required ? ' required' : '';

        switch ($type) {
            case 'checkbox':
                printf(
                    '<input type="checkbox" id="%s" name="fields[%s]" value="1" %s />',
                    esc_attr($id),
                    esc_attr($name),
                    checked((bool)$value, true, false)
                );
                break;

            case 'textarea':
                printf(
                    '<textarea id="%s" name="fields[%s]" rows="4" class="large-text"%s>%s</textarea>',
                    esc_attr($id),
                    esc_attr($name),
                    esc_attr($req),
                    esc_textarea((string)($value ?? ''))
                );
                break;

            case 'select':
                $options = $this->selectOptions($field);
                printf('<select id="%s" name="fields[%s]"%s>', esc_attr($id), esc_attr($name), esc_attr($req));
                printf('<option value="">%s</option>', esc_html__('— none —', 'bailaya'));
                foreach ($options as $optValue => $optLabel) {
                    printf(
                        '<option value="%s" %s>%s</option>',
                        esc_attr((string)$optValue),
                        selected((string)$value, (string)$optValue, false),
                        esc_html((string)$optLabel)
                    );
                }
                echo '</select>';
                break;

            case 'number':
                printf(
                    '<input type="number" id="%s" name="fields[%s]" value="%s" step="%s" class="regular-text"%s />',
                    esc_attr($id),
                    esc_attr($name),
                    esc_attr((string)($value ?? '')),
                    esc_attr((string)($field['step'] ?? '1')),
                    esc_attr($req)
                );
                break;

            default: // text, email, date, time
                printf(
                    '<input type="%s" id="%s" name="fields[%s]" value="%s" class="regular-text"%s />',
                    esc_attr($type),
                    esc_attr($id),
                    esc_attr($name),
                    esc_attr((string)($value ?? '')),
                    esc_attr($req)
                );
        }

        if (!empty($field['help'])) {
            echo '<p class="description">' . esc_html((string)$field['help']) . '</p>';
        }

        echo '</td></tr>';
    }

    /**
     * Fixed options, or options pulled from the API for `options_from` selects.
     *
     * @param array<string,mixed> $field
     * @return array<string,string>
     */
    private function selectOptions(array $field): array
    {
        if (isset($field['options'])) {
            return $field['options'];
        }

        $source = $field['options_from'] ?? null;
        if (!$source) {
            return [];
        }

        try {
            $client = ClientFactory::make();
            $rows = match ($source) {
                'team'         => $client->listTeam(['limit' => 100]),
                'rooms'        => $client->listRooms(['limit' => 100]),
                'locations'    => $client->listLocations(),
                'studio-types' => $client->listStudioTypes(['limit' => 100]),
                default        => [],
            };
        } catch (\Throwable) {
            // A failed lookup shouldn't take the whole form down.
            return [];
        }

        $options = [];
        foreach ($rows as $row) {
            $label = trim((string)($row->name ?? '') . ' ' . (string)($row->lastname ?? ''));
            $options[(string)$row->id] = $label !== '' ? $label : (string)$row->id;
        }
        return $options;
    }

    // ─── Save / delete ─────────────────────────────────────────────────────────

    public function handleSave(): void
    {
        $key = isset($_POST['resource']) ? sanitize_key((string)$_POST['resource']) : '';
        $resource = Resources::get($key);

        if (!$resource || !current_user_can(self::CAPABILITY)) {
            wp_die(esc_html__('You do not have permission to do that.', 'bailaya'));
        }
        check_admin_referer('bailaya_save_' . $key);

        $id = isset($_POST['id']) ? sanitize_text_field(wp_unslash((string)$_POST['id'])) : '';
        $isEdit = $id !== '';

        // Sanitized in collectInput(), which casts or sanitize_text_field()s every
        // value and only reads the keys the resource declares — anything else in the
        // payload is ignored. The nonce is verified by check_admin_referer() above.
        // phpcs:disable WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
        $submitted = isset($_POST['fields']) && is_array($_POST['fields'])
            ? wp_unslash($_POST['fields'])
            : [];
        // phpcs:enable WordPress.Security.ValidatedSanitizedInput.InputNotSanitized

        $input = $this->collectInput($resource, $submitted, $isEdit);

        try {
            $client = ClientFactory::make();

            if ($isEdit) {
                if (!isset($resource['update'])) {
                    throw new \RuntimeException(__('This resource cannot be updated via the API.', 'bailaya'));
                }
                ($resource['update'])($client, $id, $input);
                $message = sprintf(
                    /* translators: %s: resource name, e.g. "Class" or "Instructor" */
                    __('%s updated.', 'bailaya'),
                    (string)$resource['singular']
                );
            } else {
                ($resource['create'])($client, $input);
                $message = sprintf(
                    /* translators: %s: resource name, e.g. "Class" or "Instructor" */
                    __('%s created.', 'bailaya'),
                    (string)$resource['singular']
                );
            }

            $this->redirect($key, $message, false);
        } catch (\Throwable $e) {
            $this->redirect($key, $e->getMessage(), true);
        }
    }

    public function handleDelete(): void
    {
        $key = isset($_GET['resource']) ? sanitize_key((string)$_GET['resource']) : '';
        $id  = isset($_GET['id']) ? sanitize_text_field(wp_unslash((string)$_GET['id'])) : '';
        $resource = Resources::get($key);

        if (!$resource || !current_user_can(self::CAPABILITY)) {
            wp_die(esc_html__('You do not have permission to do that.', 'bailaya'));
        }
        check_admin_referer('bailaya_delete_' . $key . '_' . $id);

        try {
            ($resource['delete'])(ClientFactory::make(), $id);
            $this->redirect($key, sprintf(
                /* translators: %s: resource name, e.g. "Class" or "Instructor" */
                __('%s deleted.', 'bailaya'),
                (string)$resource['singular']
            ), false);
        } catch (\Throwable $e) {
            $this->redirect($key, $e->getMessage(), true);
        }
    }

    /**
     * Coerces the submitted strings into the types the API expects, dropping
     * blanks so an untouched optional field isn't sent as "".
     *
     * @param array<string,mixed> $resource
     * @param array<string,mixed> $submitted
     * @return array<string,mixed>
     */
    private function collectInput(array $resource, array $submitted, bool $isEdit): array
    {
        $input = [];

        foreach ($resource['fields'] as $field) {
            $name = (string)$field['name'];
            $type = (string)($field['type'] ?? 'text');

            if ($isEdit && ($field['create_only'] ?? false)) continue;
            if (!$isEdit && ($field['edit_only'] ?? false)) continue;

            if ($type === 'checkbox') {
                // An unchecked box submits nothing at all.
                $input[$name] = isset($submitted[$name]);
                continue;
            }

            $raw = isset($submitted[$name]) ? trim((string)$submitted[$name]) : '';
            if ($raw === '') {
                continue;
            }

            $input[$name] = match ($field['cast'] ?? null) {
                'int'   => (int)$raw,
                'float' => (float)$raw,
                default => sanitize_text_field($raw),
            };
        }

        return $input;
    }

    private function redirect(string $key, string $message, bool $isError): void
    {
        wp_safe_redirect(add_query_arg(
            [
                'page' => 'bailaya-' . $key,
                'bailaya_message' => rawurlencode($message),
                'bailaya_status' => $isError ? 'error' : 'success',
            ],
            admin_url('admin.php')
        ));
        exit;
    }
}
