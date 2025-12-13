<?php
if (!defined('ABSPATH')) {
    exit;
}

class IBurger_Passport_Updater {
    private $slug;
    private $plugin_data;
    private $username;
    private $repo;
    private $plugin_file;
    private $github_response;

    public function __construct($plugin_file, $github_username, $github_repo) {
        $this->plugin_file = $plugin_file;
        $this->username = $github_username;
        $this->repo = $github_repo;
        $this->slug = dirname(plugin_basename($this->plugin_file));
        $this->plugin_data = get_plugin_data($this->plugin_file);

        add_filter('pre_set_site_transient_update_plugins', array($this, 'check_update'));
        add_filter('plugins_api', array($this, 'plugin_popup'), 10, 3);
        add_filter('upgrader_post_install', array($this, 'after_install'), 10, 3);
    }

    public function check_update($transient) {
        if (empty($transient->checked)) {
            return $transient;
        }

        $remote_version = $this->get_github_version();

        if ($remote_version && version_compare($this->plugin_data['Version'], $remote_version, '<')) {
            $res = new stdClass();
            $res->slug = $this->slug;
            $res->plugin = plugin_basename($this->plugin_file);
            $res->new_version = $remote_version;
            $res->url = $this->plugin_data['PluginURI'];
            $res->package = $this->get_github_download_url();

            $transient->response[$res->plugin] = $res;
        }

        return $transient;
    }

    public function plugin_popup($result, $action, $args) {
        if ($action !== 'plugin_information' || empty($args->slug) || $args->slug !== $this->slug) {
            return $result;
        }

        $github_data = $this->get_github_data();

        $res = new stdClass();
        $res->name = $this->plugin_data['Name'];
        $res->slug = $this->slug;
        $res->version = $this->get_github_version();
        $res->author = $this->plugin_data['Author'];
        $res->homepage = $this->plugin_data['PluginURI'];
        $res->requires = $this->plugin_data['RequiresWP'];
        $res->tested = $this->plugin_data['TestedUpTo'];
        $res->download_link = $this->get_github_download_url();
        $res->trunk = $this->get_github_download_url();
        $res->last_updated = $github_data ? $github_data->published_at : date('Y-m-d');
        
        $res->sections = array(
            'description' => $this->plugin_data['Description'],
            'changelog' => $this->get_github_changelog(),
        );

        return $res;
    }

    public function after_install($response, $hook_extra, $result) {
        global $wp_filesystem;

        $install_directory = plugin_dir_path($this->plugin_file);
        $wp_filesystem->move($result['destination'], $install_directory);
        $result['destination'] = $install_directory;

        return $result;
    }

    private function get_github_version() {
        $data = $this->get_github_data();
        return $data ? str_replace('v', '', $data->tag_name) : false;
    }

    private function get_github_download_url() {
        $data = $this->get_github_data();
        return $data ? $data->zipball_url : false;
    }
    
    private function get_github_changelog() {
        $data = $this->get_github_data();
        if ($data && !empty($data->body)) {
            // Parse Markdown to HTML if needed, or return plain text
            return nl2br($data->body);
        }
        return 'No changelog available.';
    }

    private function get_github_data() {
        if (!empty($this->github_response)) {
            return $this->github_response;
        }

        $url = "https://api.github.com/repos/{$this->username}/{$this->repo}/releases/latest";
        
        $response = wp_remote_get($url, array(
            'headers' => array(
                'Accept' => 'application/vnd.github.v3+json',
                'User-Agent' => 'WordPress/' . get_bloginfo('version') . '; ' . home_url()
            )
        ));

        if (is_wp_error($response)) {
            return false;
        }

        $body = wp_remote_retrieve_body($response);
        $this->github_response = json_decode($body);

        return $this->github_response;
    }
}

