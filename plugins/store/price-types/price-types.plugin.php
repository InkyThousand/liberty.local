<?php defined('MONSTRA_ACCESS') or die('No direct script access.') ?>
<?php
Plugin::register( __FILE__,
                __('Price Types', 'price-types'),
                __('Price types management plugin', 'price-types'),
                '1.0.0',
                'razorolog',
                '',
                null,
                'store');

Plugin::Admin('price-types', 'store');