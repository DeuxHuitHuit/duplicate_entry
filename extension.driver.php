<?php
	
	require_once(TOOLKIT . '/class.sectionmanager.php');
	
	class Extension_Duplicate_Entry extends Extension {
		
		public function about() {
			return array(
				'name'			=> 'Duplicate Entry',
				'version'		=> '1.0',
				'release-date'	=> '2009-09-23',
				'author'		=> array(
					'name'			=> 'Nick Dunn',
					'website'		=> 'http://nick-dunn.co.uk'
				),
				'description'	=> 'Duplicate an existing entry using a "Save as New" button'
			);
		}
		
		public function getSubscribedDelegates() {
			return array(
				array(
					'page'		=> '/backend/',
					'delegate'	=> 'InitaliseAdminPageHead',
					'callback'	=> 'initaliseAdminPageHead'
				)
			);
		}
		
		private function serialiseSectionSchema($section) {
			$current_section_fields = $section->fetchFieldsSchema();
			foreach($current_section_fields as $i => $field) {
				unset($current_section_fields[$i]['id']);
				unset($current_section_fields[$i]['location']);
			}
			return md5(serialize($current_section_fields));
		}

		public function initaliseAdminPageHead($context) {
			$page = $context['parent']->Page;
			
			if ($page instanceof contentPublish and $page->_context['page'] == 'edit') {
				$page->addScriptToHead(URL . '/extensions/duplicate_entry/assets/duplicate_entry.js', 123456789);
				
				$sm = new SectionManager(Administration::instance());
				
				$callback = Administration::instance()->getPageCallback();
				$current_section = $sm->fetch($sm->fetchIDFromHandle($callback['context']['section_handle']));
				$current_section_hash = $this->serialiseSectionSchema($current_section);
				
				$duplicate_sections = array();
				
				foreach($sm->fetch() as $section) {
					$section_hash = $this->serialiseSectionSchema($section);
					if ($section_hash == $current_section_hash) {
						$duplicate_sections[$section->get('handle')] = $section->get('name');
					}
				}
				
				$json = 'var duplicate_sections = ' . json_encode($duplicate_sections);
				$script = new XMLElement('script', $json, array('type' => 'text/javascript'));
				$page->addElementToHead($script);
				
			}
		}
	}
	
?>