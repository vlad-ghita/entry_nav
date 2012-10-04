<?php

	Class Extension_Entry_Nav extends Extension
	{



		/*------------------------------------------------------------------------------------------------*/
		/*  Delegates  */
		/*------------------------------------------------------------------------------------------------*/

		public function getSubscribedDelegates(){
			return array(
				array(
					'page' => '/backend/',
					'delegate' => 'InitaliseAdminPageHead',
					'callback' => 'dInitaliseAdminPageHead'
				),

				array(
					'page' => '/backend/',
					'delegate' => 'AdminPagePreGenerate',
					'callback' => 'dAdminPagePreGenerate'
				)
			);
		}

		public function dInitaliseAdminPageHead(){
			$callback = Administration::instance()->getPageCallback();

			// append assets
			if( $callback['context']['page'] == 'edit' ){
				Administration::instance()->Page->addStylesheetToHead( URL.'/extensions/entry_nav/assets/entry_nav.publish_single.css', "screen" );
			}
		}

		public function dAdminPagePreGenerate($context){
			$callback = Administration::instance()->getPageCallback();

			if( $callback['context']['page'] === 'edit' ){
				/** @var $cxt XMLElement */
				$cxt = $context['oPage']->Context;
				if( !$cxt instanceof XMLElement ) return;

				$actions = $cxt->getChildByName( 'ul', 0 );
				if( !$actions instanceof XMLElement ) return;

				// fetch entries
				$section_id = SectionManager::fetchIDFromHandle( $callback['context']['section_handle'] );
				$section = SectionManager::fetch( $section_id );

				EntryManager::setFetchSorting( $section->getSortingField(), $section->getSortingOrder() );
				$entries = EntryManager::fetch( null, $section_id, null, null, null, null, null, false, false );

				// get next and prev
				$entry_id = $prev_id = $next_id = $callback['context']['entry_id'];

				$count = count( $entries );
				for( $i = 0 ; $i < $count ; $i++ )
					if( $entries[$i]['id'] == $entry_id ){
						$prev_id = $i == 0 ? $entries[$count - 1]['id'] : $entries[$i - 1]['id'];
						$next_id = $i == $count - 1 ? $entries[0]['id'] : $entries[$i + 1]['id'];
						break;
					}

				if( $prev_id == $entry_id && $next_id == $entry_id ) return;

				// add buttons
				$li = new XMLelement('li', null, array('class' => 'entry-nav'));

				if( $prev_id !== $entry_id )
					$li->appendChild( Widget::Anchor(
						__( '&larr; Previous' ),
						SYMPHONY_URL.$callback['pageroot'].'edit/'.$prev_id,
						null,
						'button entry-nav-prev',
						null,
						array('accesskey' => 'z')
					) );

				if( $next_id !== $entry_id )
					$li->appendChild( Widget::Anchor(
						__( 'Next &rarr;' ),
						SYMPHONY_URL.$callback['pageroot'].'edit/'.$next_id,
						null,
						'button entry-nav-next',
						null,
						array('accesskey' => 'x')
					) );

				$actions->appendChild( $li );
			}
		}

	}
