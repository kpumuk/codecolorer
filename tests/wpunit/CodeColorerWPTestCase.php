<?php
/*
CodeColorer WordPress integration tests
https://kpumuk.info/projects/wordpress-plugins/codecolorer
*/

abstract class CodeColorerWPTestCase extends WP_UnitTestCase
{
    public function set_up()
    {
        parent::set_up();

        $this->resetCodeColorerOptions();
        update_option('comment_registration', 0);
        update_option('comment_moderation', 0);
        update_option('comment_previously_approved', 0);
        update_option('require_name_email', 0);
    }

    protected function resetCodeColorerOptions()
    {
        $defaults = array(
            'codecolorer_css_style' => '',
            'codecolorer_css_class' => '',
            'codecolorer_lines_to_scroll' => 20,
            'codecolorer_width' => 435,
            'codecolorer_height' => 300,
            'codecolorer_rss_width' => 435,
            'codecolorer_line_numbers' => false,
            'codecolorer_disable_keyword_linking' => false,
            'codecolorer_tab_size' => 4,
            'codecolorer_theme' => '',
            'codecolorer_inline_theme' => '',
        );

        foreach ($defaults as $name => $value) {
            update_option($name, $value);
        }
    }

    protected function renderContent($content)
    {
        return apply_filters('the_content', $content);
    }

    protected function createApprovedComment($post_id, $content)
    {
        $comment_id = wp_new_comment(
            wp_slash(
                array(
                    'comment_post_ID' => $post_id,
                    'comment_author' => 'CodeColorer Test',
                    'comment_author_email' => 'codecolorer@example.com',
                    'comment_author_url' => 'https://example.com',
                    'comment_content' => $content,
                    'comment_type' => '',
                    'user_id' => 0,
                    'comment_author_IP' => '127.0.0.1',
                    'comment_agent' => 'phpunit',
                )
            ),
            true
        );

        $this->assertNotWPError($comment_id);
        wp_set_comment_status($comment_id, 'approve');

        return $comment_id;
    }

    protected function renderComment($comment_id)
    {
        $comment = get_comment($comment_id);

        return apply_filters('comment_text', $comment->comment_content, $comment, array());
    }
}
