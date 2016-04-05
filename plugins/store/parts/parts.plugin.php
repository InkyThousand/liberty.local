<?php defined('MONSTRA_ACCESS') or die('No direct script access.') ?>
<?php
Plugin::register( __FILE__,
                __('Parts', 'parts'),
                __('Parts management plugin', 'parts'),
                '1.0.0',
                'razorolog',
                '',
                null,
                'store');

Plugin::Admin('parts', 'store');

Javascript::add('plugins/store/parts/js/' . Option::get('language') . '.parts.js', 'backend');