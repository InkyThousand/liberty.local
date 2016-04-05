<?php defined('MONSTRA_ACCESS') or die('No direct script access.') ?>
<?php Chunk::get('header') ?>
                <div id="content-container">
                    <div id="side-bar">
                        <div id="content-header"><img src="<?php echo Site::themeRoot() ?>/Images/SitePages/TitleImages/Home.gif" alt=" " style="height:54px;width:248px;border-width:0px"/></div>
                        <div class="child-nav">
                        <div class="child-nav-content">
                        <div class="child-nav">
                        <?php echo Menu::get() ?>
                        </div>
                        </div>
                        </div>
                    </div>
                    <div id="main-content">
                        <div id="content-image"><img src="<?php echo Site::themeRoot() ?>/Images/SitePages/BannerImages/Default.jpg" alt=" " style="height:191px;width:692px;border-width:0px"/></div>
                        <div id="content-body">
                        <?php echo Site::content() ?>
                        </div>
                    </div>
                </div>
            </div>
            <div style="clear:both"></div>
<?php Chunk::get('footer') ?>