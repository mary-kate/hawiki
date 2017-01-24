<?php
/**
 * MediaWiki skin for Haskell Wiki <https://wiki.haskell.org/>
 *
 * @file
 * @ingroup Skins
 */

/**
 * Inherit main code from SkinTemplate, set the CSS and template filter.
 *
 * @ingroup Skins
 */
class SkinHawiki extends SkinTemplate {
	public $skinname = 'hawiki', $stylename = 'hawiki',
		$template = 'HawikiTemplate', $useHeadElement = true;

	/**
	 * This is where we could add things like custom <meta> tags or load this
	 * skin's custom JavaScript modules here, but right now this skin has
	 * neither, so we don't really do anything fancy here. But maybe one day
	 * there's a reason for this skin to have custom JS, who knows.
	 *
	 * @param OutputPage $out
	 */
	public function initPage( OutputPage $out ) {
		parent::initPage( $out );
	}

	/**
	 * Add CSS via ResourceLoader
	 *
	 * @param OutputPage $out
	 */
	function setupSkinUserCss( OutputPage $out ) {
		parent::setupSkinUserCss( $out );

		// Need to use addModuleStyles() instead of addModules() for proper
		// RTL support
		$out->addModuleStyles( array(
			'mediawiki.skinning.interface',
			'mediawiki.skinning.content.externallinks',
			'skins.hawiki'
		) );
	}
}

/**
 * @todo document
 * @ingroup Skins
 */
class HawikiTemplate extends BaseTemplate {
	/**
	 * Template filter callback for Hawiki skin.
	 * Takes an associative array of data set from a SkinTemplate-based
	 * class, and a wrapper for MediaWiki's localization database, and
	 * outputs a formatted page.
	 */
	function execute() {
		$this->skin = $this->data['skin'];

		$this->data['pageLanguage'] =
			$this->skin->getTitle()->getPageViewLanguage()->getHtmlCode();
		$this->html( 'headelement' );
?>
	<div id="topbar" class="noprint">
		<div class="portlet noprint" id="p-personal" role="navigation">
			<h3><?php $this->msg( 'personaltools' ) ?></h3>

			<div class="pBody">
				<ul<?php $this->html( 'userlangattributes' ) ?>>
				<li><a class="homebutton" href="<?php echo htmlspecialchars( $this->data['nav_urls']['mainpage']['href'] ) ?>"><?php $this->msg( 'hawiki-home' ) ?></a></li>
					<?php
					$personalTools = $this->getPersonalTools();

					if ( array_key_exists( 'uls', $personalTools ) ) {
						echo $this->makeListItem( 'uls', $personalTools['uls'] );
						unset( $personalTools['uls'] );
					}

					if ( !$this->skin->getUser()->isLoggedIn() &&
						User::groupHasPermission( '*', 'edit' ) ) {

						echo Html::rawElement( 'li', array(
							'id' => 'pt-anonuserpage'
						), $this->getMsg( 'notloggedin' )->escaped() );
					}

					foreach ( $personalTools as $key => $item ) {
						echo $this->makeListItem( $key, $item );
					}
					?>
				</ul>
			</div>
		</div>
		<?php $this->searchBox(); ?>
	</div>
	<div id="globalWrapper"<?php if ( $this->skin->getTitle()->isMainPage() ) { ?> class="homepage" <?php } ?>>
		<div class="portlet" id="p-logo" role="banner">
			<?php
			echo Html::element( 'a', array(
					'href' => $this->data['nav_urls']['mainpage']['href'],
					'class' => 'mw-wiki-logo',
					)
					+ Linker::tooltipAndAccesskeyAttribs( 'p-logo' )
			); ?>
		</div>
	<div id="column-content">
		<div id="notice-area" class="noprint">
			<?php if ( $this->data['sitenotice'] ) { ?><div id="siteNotice"><?php $this->html( 'sitenotice' ) ?></div><?php } ?>
			<?php if ( $this->data['undelete'] ) { ?><div id="contentSub2"><?php $this->html( 'undelete' ) ?></div><?php } ?>
			<?php if ( $this->data['newtalk'] ) { ?><div class="usermessage"><?php $this->html( 'newtalk' )  ?></div><?php } ?>
		</div>
		<div id="content-wrapper">
			<?php $this->cactions(); ?>
			<div id="content" class="mw-body" role="main">
				<a id="top"></a>
				<?php
				echo $this->getIndicators();
				// Loose comparison with '!=' is intentional, to catch null and false too, but not '0'
				if ( $this->data['title'] != '' ) {
				?>
					<h1 id="firstHeading" class="firstHeading" lang="<?php $this->text( 'pageLanguage' ); ?>"><?php $this->html( 'title' ) ?></h1>
				<?php } ?>
				<div id="bodyContent" class="mw-body-content">
					<h3 id="siteSub"><?php $this->msg( 'tagline' ) ?></h3>
					<div id="contentSub"><?php $this->html( 'subtitle' ) ?></div>
					<div id="jump-to-nav" class="mw-jump"><?php $this->msg( 'jumpto' ) ?> <a href="#column-one"><?php $this->msg( 'jumptonavigation' ) ?></a><?php $this->msg( 'comma-separator' ) ?><a href="#searchInput"><?php $this->msg( 'jumptosearch' ) ?></a></div>
					<!-- start content -->
					<?php
					$this->html( 'bodytext' );
					if ( $this->data['catlinks'] ) {
						$this->html( 'catlinks' );
					}
					?>
					<!-- end content -->
					<?php
					if ( $this->data['dataAfterContent'] ) {
						$this->html( 'dataAfterContent' );
					}
					?>
					<div class="visualClear"></div>
				</div>
			</div>
		</div>
	</div>
	<div id="column-one">
<?php
		$this->renderPortals( $this->data['sidebar'] );
?>
	</div><!-- end of the left (by default at least) column -->
	<div class="visualClear"></div>
		<?php
		$validFooterIcons = $this->getFooterIcons( 'icononly' );
		$validFooterLinks = $this->getFooterLinks( 'flat' ); // Additional footer links

		if ( count( $validFooterIcons ) + count( $validFooterLinks ) > 0 ) {
			?>
			<div id="footer" role="contentinfo"<?php $this->html( 'userlangattributes' ) ?>>
			<?php
			$footerEnd = '</div>';
		} else {
			$footerEnd = '';
		}

		foreach ( $validFooterIcons as $blockName => $footerIcons ) {
			?>
			<div id="f-<?php echo htmlspecialchars( $blockName ); ?>ico">
				<?php
				foreach ( $footerIcons as $icon ) {
					echo $this->skin->makeFooterIcon( $icon );
				}
				?>
			</div>
		<?php
		}

		if ( count( $validFooterLinks ) > 0 ) {
			?>
			<ul id="f-list">
				<?php
				foreach ( $validFooterLinks as $aLink ) {
					?>
					<li id="<?php echo $aLink ?>"><?php $this->html( $aLink ) ?></li>
				<?php
				}
				?>
			</ul>
		<?php
		}

		echo $footerEnd;
		?>
	</div>
	<?php $this->printTrail(); ?>
</body>
</html>
<?php
	} // end of execute() method

	/**
	 * @param array $sidebar
	 */
	protected function renderPortals( $sidebar ) {
		if ( !isset( $sidebar['SEARCH'] ) ) {
			$sidebar['SEARCH'] = true;
		}
		if ( !isset( $sidebar['TOOLBOX'] ) ) {
			$sidebar['TOOLBOX'] = true;
		}
		if ( !isset( $sidebar['LANGUAGES'] ) ) {
			$sidebar['LANGUAGES'] = true;
		}

		foreach ( $sidebar as $boxName => $content ) {
			if ( $content === false ) {
				continue;
			}

			// Numeric strings gets an integer when set as key, cast back - T73639
			$boxName = (string)$boxName;

			if ( $boxName == 'SEARCH' ) {
				//$this->searchBox();
			} elseif ( $boxName == 'TOOLBOX' ) {
				$this->toolbox();
			} elseif ( $boxName == 'LANGUAGES' ) {
				$this->languageBox();
			} else {
				$this->customBox( $boxName, $content );
			}
		}
	}

	function searchBox() {
		?>
		<div id="p-search" class="portlet" role="search">
			<!--<h3><label for="searchInput"><?php $this->msg( 'search' ) ?></label></h3>-->
			<div id="searchBody" class="pBody">
				<form action="<?php $this->text( 'wgScript' ) ?>" id="searchform">
					<input type="hidden" name="title" value="<?php $this->text( 'searchtitle' ) ?>"/>
					<?php echo $this->makeSearchInput( array( 'id' => 'searchInput' ) ); ?>

					<?php
					echo $this->makeSearchButton(
						'go',
						array( 'id' => 'searchGoButton', 'class' => 'searchButton' )
					);

					if ( $this->config->get( 'UseTwoButtonsSearchForm' ) ) {
						?>&#160;
						<?php echo $this->makeSearchButton(
							'fulltext',
							array( 'id' => 'mw-searchButton', 'class' => 'searchButton' )
						);
					} else {
						?>

						<div><a href="<?php
						$this->text( 'searchaction' )
						?>" rel="search"><?php $this->msg( 'powersearch-legend' ) ?></a></div><?php
					} ?>

				</form>

				<?php $this->renderAfterPortlet( 'search' ); ?>
			</div>
		</div>
	<?php
	}

	/**
	 * Prints the content actions bar.
	 * Shared between MonoBook and Modern
	 */
	function cactions() {
		// ashley: added noprint class here
		?>
		<div id="p-cactions" class="portlet noprint" role="navigation">
			<h3><?php $this->msg( 'views' ) ?></h3>

			<div class="pBody">
				<ul><?php
					foreach ( $this->data['content_actions'] as $key => $tab ) {
						echo '
				' . $this->makeListItem( $key, $tab );
					} ?>

				</ul>
				<?php $this->renderAfterPortlet( 'cactions' ); ?>
			</div>
		</div>
	<?php
	}

	/*************************************************************************************************/
	function toolbox() {
		?>
		<div class="portlet" id="p-tb" role="navigation">
			<h3><?php $this->msg( 'toolbox' ) ?></h3>

			<div class="pBody">
				<ul>
					<?php
					foreach ( $this->getToolbox() as $key => $tbitem ) {
						?>
						<?php echo $this->makeListItem( $key, $tbitem ); ?>

					<?php
					}
					Hooks::run( 'HawikiTemplateToolboxEnd', array( &$this ) ); // ashley: MonoBook had MonoBookTemplateToolboxEnd here
					Hooks::run( 'SkinTemplateToolboxEnd', array( &$this, true ) );
					?>
				</ul>
				<?php $this->renderAfterPortlet( 'tb' ); ?>
			</div>
		</div>
	<?php
	}

	/*************************************************************************************************/
	function languageBox() {
		if ( $this->data['language_urls'] !== false ) {
			?>
			<div id="p-lang" class="portlet" role="navigation">
				<h3<?php $this->html( 'userlangattributes' ) ?>><?php $this->msg( 'otherlanguages' ) ?></h3>

				<div class="pBody">
					<ul>
						<?php
						foreach ( $this->data['language_urls'] as $key => $langLink ) {
							echo $this->makeListItem( $key, $langLink );
						}
						?>
					</ul>

					<?php $this->renderAfterPortlet( 'lang' ); ?>
				</div>
			</div>
		<?php
		}
	}

	/*************************************************************************************************/
	/**
	 * @param string $bar
	 * @param array|string $cont
	 */
	function customBox( $bar, $cont ) {
		$portletAttribs = array(
			'class' => 'generated-sidebar portlet',
			'id' => Sanitizer::escapeId( "p-$bar" ),
			'role' => 'navigation'
		);

		$tooltip = Linker::titleAttrib( "p-$bar" );
		if ( $tooltip !== false ) {
			$portletAttribs['title'] = $tooltip;
		}
		echo '	' . Html::openElement( 'div', $portletAttribs );
		$msgObj = wfMessage( $bar );
		?>

		<h3><?php echo htmlspecialchars( $msgObj->exists() ? $msgObj->text() : $bar ); ?></h3>
		<div class="pBody">
			<?php
			if ( is_array( $cont ) ) {
				?>
				<ul>
					<?php
					foreach ( $cont as $key => $val ) {
						echo $this->makeListItem( $key, $val );
					}
					?>
				</ul>
			<?php
			} else {
				# allow raw HTML block to be defined by extensions
				echo $cont;
			}

			$this->renderAfterPortlet( $bar );
			?>
		</div>
		</div>
	<?php
	}
} // end of class