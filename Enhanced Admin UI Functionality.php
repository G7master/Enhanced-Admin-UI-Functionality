<?php
/*
Plugin Name: Enhanced WP Admin UI Functionality
Plugin URI: https://github.com/G7master/Enhanced-Admin-UI-Functionality
Description: Custom Admin UI Functions, Editor Enhancements, and Page/Post Filters. Includes LearnPress and Eduma tweaks for course management.
Version: 1.0
Author: George Tumanishvili
Author URI: https://www.linkedin.com/in/georgetumanishvili/
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html
Text Domain: enhanced-admin-ui-functionality
*/

// ------------------------------
// Course Categories: Show Parent Column in Admin
// ------------------------------
add_filter('manage_edit-course_category_columns', 'add_course_category_parent_column');
if (!function_exists('add_course_category_parent_column')) {
    function add_course_category_parent_column($columns) {
        $columns['parent'] = __('Parent Category');
        return $columns;
    }
}

add_filter('manage_course_category_custom_column', 'show_course_category_parent_column', 10, 3);
if (!function_exists('show_course_category_parent_column')) {
    function show_course_category_parent_column($out, $column_name, $term_id) {
        if ($column_name === 'parent') {
            $term = get_term($term_id, 'course_category');
            if ($term && $term->parent) {
                $parent = get_term($term->parent, 'course_category');
                $out .= $parent ? '<a href="' . esc_url(get_edit_term_link($parent->term_id, 'course_category')) . '">' . esc_html($parent->name) . '</a>' : '';
            } else {
                $out .= __('None');
            }
        }
        return $out;
    }
}
// ------------------------------
// Enhance Classic Editor: Font family, size, and background color (system fonts only)
add_filter('mce_buttons', function($buttons) {
    array_unshift($buttons, 'fontselect', 'fontsizeselect'); // Font family and size
    if (!in_array('backcolor', $buttons)) {
        $buttons[] = 'backcolor'; // Background color
    }
    return $buttons;
});

// Define font sizes and system-safe fonts (no external fonts)
add_filter('tiny_mce_before_init', function($init) {
    // Font sizes from 10px to 48px
    $init['fontsize_formats'] = '10px 11px 12px 13px 14px 16px 18px 20px 24px 28px 32px 36px 42px 48px';

    // System fonts only — cross-platform safe
    $init['font_formats'] =
		'Arial=arial,helvetica,sans-serif;' .
        'Helvetica=helvetica,arial,sans-serif;' .
        'Georgia=georgia,palatino,serif;' .
        'Times New Roman=times new roman,times,serif;' .
        'Trebuchet MS=trebuchet ms,geneva,sans-serif;' .
        'Verdana=verdana,geneva,sans-serif;' .
        'Courier New=courier new,courier,monospace;' .
        'Lucida Console=lucida console,monaco,monospace;' .
        'Tahoma=tahoma,arial,helvetica,sans-serif;' .
        'Impact=impact,chicago;' .
        'Segoe UI=segoe ui,sans-serif;' .
        'Comic Sans MS=comic sans ms,cursive;' .
        'Poppins=Poppins,sans-serif;' .
        'Lato=Lato,sans-serif;' .
        'Roboto=Roboto,sans-serif;';

    return $init;
});

// LearnPress Courses: Add "Tags" Column in Admin List
// ------------------------------
add_filter('manage_lp_course_posts_columns', 'custom_add_lp_course_tags_column');
if (!function_exists('custom_add_lp_course_tags_column')) {
    function custom_add_lp_course_tags_column($columns) {
        $columns['course_tag'] = __('Tags', 'enhanced-admin-ui-functionality');
        return $columns;
    }
}

add_action('manage_lp_course_posts_custom_column', 'custom_display_lp_course_tags_column', 10, 2);
if (!function_exists('custom_display_lp_course_tags_column')) {
    function custom_display_lp_course_tags_column($column, $post_id) {
        if ($column === 'course_tag') {
            $terms = get_the_term_list($post_id, 'course_tag', '', ', ', '');
            echo is_string($terms) ? $terms : __('—', 'enhanced-admin-ui-functionality');
        }
    }
}
// Add Underline and Unlink buttons to Classic Editor toolbar
add_filter('mce_buttons', function($buttons) {
    if (!in_array('underline', $buttons)) {
        $buttons[] = 'underline';
    }
    if (!in_array('unlink', $buttons)) {
        $buttons[] = 'unlink';
    }
    return $buttons;
});

add_filter('mce_buttons', function() {
    return [
        'fontselect',         // ✅ 1. Font family
        'fontsizeselect',     // ✅ 2. Font size
        'formatselect',       // ✅ 3. Paragraph/Heading
        'bold', 'italic', 'underline',
        'code' , 
	'alignleft', 'aligncenter', 'alignright',
        'bullist', 'numlist',
        'link', 'unlink', 'subscript' , 'superscript' , 'alignjustify' , 'image', 'media' ,
        'backcolor', 'table' ,
    ];
}, 99);

// ------------------------------
// LearnPress Courses: Add "Tags" Column in Admin List
// ------------------------------
add_filter('manage_lp_course_posts_columns', 'custom_add_lp_course_tags_column');
if (!function_exists('custom_add_lp_course_tags_column')) {
    function custom_add_lp_course_tags_column($columns) {
        $columns['course_tag'] = __('Tags', 'enhanced-admin-ui-functionality');
        return $columns;
    }
}

add_action('manage_lp_course_posts_custom_column', 'custom_display_lp_course_tags_column', 10, 2);
if (!function_exists('custom_display_lp_course_tags_column')) {
    function custom_display_lp_course_tags_column($column, $post_id) {
        if ($column === 'course_tag') {
            $terms = get_the_term_list($post_id, 'course_tag', '', ', ', '');
            echo is_string($terms) ? $terms : __('—', 'enhanced-admin-ui-functionality');
        }
    }
}

// ------------------------------
// Bulk Actions: Draft / Set Publish Date / Set Modified Date
// ------------------------------
foreach (['post', 'page', 'lp_course'] as $type) {
    add_filter("bulk_actions-edit-$type", function($bulk_actions) {
        $bulk_actions['set_to_draft'] = __('Set Status to Draft');
        $bulk_actions['set_date_today'] = __('Set Publish Date to Today');
        $bulk_actions['set_modified_today'] = __('Set Modified Date to Today');
        return $bulk_actions;
    });

    add_filter("handle_bulk_actions-edit-$type", function($redirect_to, $doaction, $post_ids) {
        switch ($doaction) {
            case 'set_to_draft':
                foreach ($post_ids as $post_id) {
                    wp_update_post(['ID' => $post_id, 'post_status' => 'draft']);
                }
                return add_query_arg('set_to_draft', count($post_ids), $redirect_to);

            case 'set_date_today':
                foreach ($post_ids as $post_id) {
                    $post = get_post($post_id);
                    wp_update_post([
                        'ID' => $post_id,
                        'post_date' => current_time('mysql'),
                        'post_date_gmt' => current_time('mysql', 1),
                        'post_status' => ($post->post_status === 'draft') ? 'publish' : $post->post_status,
                    ]);
                }
                return add_query_arg('set_date_today', count($post_ids), $redirect_to);

            case 'set_modified_today':
                foreach ($post_ids as $post_id) {
                    wp_update_post([
                        'ID' => $post_id,
                        'post_modified' => current_time('mysql'),
                        'post_modified_gmt' => current_time('mysql', 1),
                    ]);
                }
                return add_query_arg('set_modified_today', count($post_ids), $redirect_to);
        }
        return $redirect_to;
    }, 10, 3);
}

// Admin notices after bulk actions
add_action('admin_notices', function() {
    if (!empty($_REQUEST['set_to_draft'])) {
        printf('<div class="notice notice-warning is-dismissible"><p>%d item(s) set to <strong>Draft</strong>.</p></div>', intval($_REQUEST['set_to_draft']));
    }
    if (!empty($_REQUEST['set_date_today'])) {
        printf('<div class="notice notice-success is-dismissible"><p>%d item(s) had <strong>Publish Date</strong> set to today.</p></div>', intval($_REQUEST['set_date_today']));
    }
    if (!empty($_REQUEST['set_modified_today'])) {
        printf('<div class="notice notice-success is-dismissible"><p>%d item(s) had <strong>Modified Date</strong> set to today.</p></div>', intval($_REQUEST['set_modified_today']));
    }
});

// ------------------------------
// Filter Posts by Status in Admin
// ------------------------------
add_action('restrict_manage_posts', function() {
    global $typenow;
    if (in_array($typenow, ['post', 'page', 'lp_course'])) {
        $statuses = ['publish' => 'Published', 'draft' => 'Draft'];
        $current = $_GET['post_status_filter'] ?? '';
        echo '<select name="post_status_filter"><option value="">' . __('All statuses') . '</option>';
        foreach ($statuses as $key => $label) {
            printf('<option value="%s"%s>%s</option>', esc_attr($key), selected($current, $key, false), esc_html($label));
        }
        echo '</select>';
    }
});

add_filter('pre_get_posts', function($query) {
    global $pagenow, $typenow;
    if ($pagenow === 'edit.php' && in_array($typenow, ['post', 'page', 'lp_course']) && !empty($_GET['post_status_filter'])) {
        $query->query_vars['post_status'] = sanitize_text_field($_GET['post_status_filter']);
    }
});

// ------------------------------
// Add Custom Columns: Status, Post ID, Modified Date
// ------------------------------
foreach (['post', 'page', 'lp_course'] as $type) {

    // Post status column
    add_filter("manage_{$type}_posts_columns", function($columns) {
        $columns['post_status'] = __('Status');
        return $columns;
    });

    add_action("manage_{$type}_posts_custom_column", function($column, $post_id) {
        if ($column === 'post_status') {
            echo '<strong>' . esc_html(ucfirst(get_post_status($post_id))) . '</strong>';
        }
    }, 10, 2);

    add_filter("manage_edit-{$type}_sortable_columns", function($columns) {
        $columns['post_status'] = 'post_status';
        return $columns;
    });

    // Post ID column
    add_filter("manage_{$type}_posts_columns", function($columns) {
        $columns['post_id'] = 'ID';
        return $columns;
    });

    add_action("manage_{$type}_posts_custom_column", function($column, $post_id) {
        if ($column === 'post_id') {
            echo $post_id;
        }
    }, 10, 2);

    // Modified Date ("M.Date") column
    add_filter("manage_edit-{$type}_columns", function($columns) {
        $new_columns = [];
        foreach ($columns as $key => $value) {
            $new_columns[$key] = $value;
            if ($key === 'date') {
                $new_columns['modified_date'] = __('M.Date', 'enhanced-admin-ui-functionality');
            }
        }
        return $new_columns;
    });

    add_action("manage_{$type}_posts_custom_column", function($column, $post_id) {
        if ($column === 'modified_date') {
            $modified = get_post_field('post_modified', $post_id);
            echo esc_html(date_i18n('Y/m/d', strtotime($modified)));
        }
    }, 10, 2);

    add_filter("manage_edit-{$type}_sortable_columns", function($columns) {
        $columns['modified_date'] = 'modified';
        return $columns;
    });
}

// Enable sorting by modified date
add_action('pre_get_posts', function($query) {
    if (is_admin() && $query->is_main_query() && $query->get('orderby') === 'modified') {
        $query->set('orderby', 'modified');
    }
});

// ------------------------------
// Admin Filtering: Multiple Tag Filters for Posts
// ------------------------------
add_action('restrict_manage_posts', function($post_type, $which) {
    if ($post_type !== 'post') {
        return;
    }

    $tags = get_terms(['taxonomy' => 'post_tag', 'hide_empty' => false]);
    if (empty($tags) || is_wp_error($tags)) {
        return;
    }

    for ($i = 1; $i <= 3; $i++) {
        $selected = $_GET["post_tag_{$i}"] ?? '';
        echo '<select name="post_tag_' . esc_attr($i) . '" class="postform">';
// translators: %d is the index number of the tag filter dropdown field
$label = esc_html__('All Tags (Field %d)', 'enhanced-admin-ui-functionality');

printf(
    '<option value="">%s</option>',
    sprintf($label, $i)
);


        foreach ($tags as $tag) {
            printf(
                '<option value="%s"%s>%s</option>',
                esc_attr($tag->slug),
                selected($selected, $tag->slug, false),
                esc_html($tag->name)
            );
        }

        echo '</select>';
    }
}, 10, 2);

add_action('pre_get_posts', function($query) {
    global $pagenow;
    if (!is_admin() || $pagenow !== 'edit.php' || ($_GET['post_type'] ?? 'post') !== 'post') {
        return;
    }

    $tags = [];
    for ($i = 1; $i <= 3; $i++) {
        if (!empty($_GET["post_tag_{$i}"])) {
            $tags[] = sanitize_text_field($_GET["post_tag_{$i}"]);
        }
    }

    if (!empty($tags)) {
        $query->query_vars['tag_slug__in'] = $tags;
    }
});

// ------------------------------
// Enable Categories on Pages
// ------------------------------
add_action('init', function() {
    register_taxonomy_for_object_type('category', 'page');
});

// ------------------------------
// LearnPress Courses: Filter by Course Category & Tag in Admin
// ------------------------------
add_action('restrict_manage_posts', function($post_type, $which) {
    if ($post_type !== 'lp_course') {
        return;
    }

    // Hide regular WP 'category' taxonomy from this screen
    global $wp_taxonomies;
    unset($wp_taxonomies['category']);

    // Course Categories
    $categories = get_terms(['taxonomy' => 'course_category', 'hide_empty' => false]);
    echo '<select name="course_category" class="postform">';
    echo '<option value="">' . __('All Course Categories') . '</option>';
    foreach ($categories as $cat) {
        printf(
            '<option value="%s"%s>%s</option>',
            esc_attr($cat->slug),
            selected($_GET['course_category'] ?? '', $cat->slug, false),
            esc_html($cat->name)
        );
    }
    echo '</select>';

    // Course Tags
    $tags = get_terms(['taxonomy' => 'course_tag', 'hide_empty' => false]);
    echo '<select name="course_tag" class="postform">';
    echo '<option value="">' . __('All Course Tags') . '</option>';
    foreach ($tags as $tag) {
        printf(
            '<option value="%s"%s>%s</option>',
            esc_attr($tag->slug),
            selected($_GET['course_tag'] ?? '', $tag->slug, false),
            esc_html($tag->name)
        );
    }
    echo '</select>';
}, 10, 2);

add_action('pre_get_posts', function($query) {
    global $pagenow;
    if (!is_admin() || $pagenow !== 'edit.php' || $query->get('post_type') !== 'lp_course') {
        return;
    }

    $tax_query = [];

    if (!empty($_GET['course_category'])) {
        $tax_query[] = [
            'taxonomy' => 'course_category',
            'field'    => 'slug',
            'terms'    => sanitize_text_field($_GET['course_category']),
        ];
    }

    if (!empty($_GET['course_tag'])) {
        $tax_query[] = [
            'taxonomy' => 'course_tag',
            'field'    => 'slug',
            'terms'    => sanitize_text_field($_GET['course_tag']),
        ];
    }

    if (!empty($tax_query)) {
        $query->set('tax_query', $tax_query);
    }
});

// Remove default Author column (keep LearnPress instructor)
add_filter('manage_lp_course_posts_columns', function($columns) {
    unset($columns['author']);
    return $columns;
}, 20);

// ------------------------------
// Enable Drag-Resizable Columns in Admin Lists
// ------------------------------
add_action('admin_footer', function() {
    global $typenow;
    if (!in_array($typenow, ['post', 'page', 'lp_course'])) return;
    ?>
    <script>
    (function () {
        const table = document.querySelector('.wp-list-table');
        if (!table) return;

        const tableId = 'resizable-columns-' + window.location.pathname + '?type=' + '<?php echo esc_js($typenow); ?>';
        const savedWidths = JSON.parse(localStorage.getItem(tableId) || '{}');

        let pressed = false, startX = 0, startWidth = 0, header = null, columnKey = null;

        table.querySelectorAll('th').forEach(th => {
            const colClass = Array.from(th.classList).find(cls => cls.startsWith('column-'));
            if (!colClass) return;

            if (savedWidths[colClass]) {
                th.style.width = savedWidths[colClass];
            }

            th.style.position = 'relative';

            const resizer = document.createElement('div');
            resizer.style.position = 'absolute';
            resizer.style.top = 0;
            resizer.style.right = 0;
            resizer.style.width = '5px';
            resizer.style.height = '100%';
            resizer.style.cursor = 'col-resize';
            resizer.style.userSelect = 'none';
            resizer.style.zIndex = 10;

            resizer.addEventListener('mousedown', e => {
                pressed = true;
                startX = e.pageX;
                startWidth = th.offsetWidth;
                header = th;
                columnKey = colClass;
                document.body.style.cursor = 'col-resize';
                e.preventDefault();
            });

            th.appendChild(resizer);
        });

        document.addEventListener('mousemove', e => {
            if (!pressed || !header || !columnKey) return;
            const newWidth = Math.max(40, startWidth + (e.pageX - startX));
            header.style.width = newWidth + 'px';
            savedWidths[columnKey] = newWidth + 'px';
            localStorage.setItem(tableId, JSON.stringify(savedWidths));
        });

        document.addEventListener('mouseup', () => {
            pressed = false;
            header = null;
            columnKey = null;
            document.body.style.cursor = '';
        });
    })();
    </script>
    <?php
});

// ------------------------------
// Register "Page Type" Taxonomy for Pages + Admin Filter
// ------------------------------
add_action('init', function() {
    register_taxonomy('page_type', 'page', [
        'label'             => __('Page Type'),
        'public'            => true,
        'rewrite'           => ['slug' => 'page-type'],
        'hierarchical'      => true,
        'show_admin_column' => true,
    ]);
});

add_action('restrict_manage_posts', function($post_type, $which) {
    if ($post_type !== 'page') return;

    $taxonomy = 'page_type';
    $terms = get_terms($taxonomy, ['hide_empty' => false]);
    if (empty($terms) || is_wp_error($terms)) return;

    $selected = $_GET[$taxonomy] ?? '';
    echo '<select name="' . esc_attr($taxonomy) . '" class="postform">';
    echo '<option value="">' . esc_html__('All Page Types') . '</option>';
    foreach ($terms as $term) {
        printf(
            '<option value="%s"%s>%s</option>',
            esc_attr($term->slug),
            selected($selected, $term->slug, false),
            esc_html($term->name)
        );
    }
    echo '</select>';
}, 10, 2);

add_action('pre_get_posts', function($query) {
    global $pagenow;
    if (!is_admin() || $pagenow !== 'edit.php' || $query->get('post_type') !== 'page') return;

    if (!empty($_GET['page_type'])) {
        $query->set('tax_query', [[
            'taxonomy' => 'page_type',
            'field'    => 'slug',
            'terms'    => sanitize_text_field($_GET['page_type']),
        ]]);
    }
});

// ------------------------------
// Admin Filtering: Author Dropdown for Posts, Pages, and Courses
// ------------------------------
add_action('restrict_manage_posts', function($post_type) {
    if (!in_array($post_type, ['post', 'page', 'lp_course'])) return;

    $selected = $_GET['filter_by_author'] ?? '';
    $authors = get_users([
        'who'     => 'authors',
        'orderby' => 'display_name',
        'order'   => 'ASC',
    ]);

    if (!empty($authors)) {
        echo '<select name="filter_by_author">';
        echo '<option value="">' . __('All Authors') . '</option>';
        foreach ($authors as $author) {
            printf(
                '<option value="%s"%s>%s</option>',
                esc_attr($author->ID),
                selected($selected, $author->ID, false),
                esc_html($author->display_name)
            );
        }
        echo '</select>';
    }
}, 10);

add_action('pre_get_posts', function($query) {
    global $pagenow;

    if (
        !is_admin() || 
        $pagenow !== 'edit.php' || 
        !in_array($_GET['post_type'] ?? 'post', ['post', 'page', 'lp_course'])
    ) return;

    if (!empty($_GET['filter_by_author'])) {
        $query->set('author', absint($_GET['filter_by_author']));
    }
});
