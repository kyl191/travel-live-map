<?php
require_once('thirdparty/simple_html_dom.php');
require_once('thirdparty/class.stemmer.inc');

class MapPage extends Page {
	protected $rebuildGlossaryCache = true;
	static $db = array(
		'EnableAutoGlossary' => 'Boolean',
		'EnableManualGlossaryCache' => 'Boolean',
		'GlossaryCache' => 'HTMLText',
		'GMapsCoords' => 'Text',
		'FAQ' => 'HTMLText',
		'DefaultZoom' => 'Text'
	);

	static $has_one = array(
		'Icon' => 'Image'
    );

    static $defaults = array(
		'EnableAutoGlossary' => 1,
		'DefaultZoom' => "6",
		'GMapsCoords' => "1.889306, 103.612061"
	);
	static $allowed_children = array('MapPointPage','MapPolygonPage');
	function getCMSFields() {
		$fields = parent::getCMSFields();
		
		$fields->addFieldToTab('Root.Content.Main', new ImageField('Icon'), 'Content');

		$fields->renameField('Content', 'Introduction');

		$fields->addFieldToTab('Root.Content.Main', new GMapsField('GMapsCoords', 'Map', 'marker'), 'Content');
		$fields->renameField('MapCenter', 'Coordinates of Map Center');

        $fields->addFieldToTab('Root.Content.Main', new TextField('DefaultZoom'), 'Content');
        $fields->renameField('DefaultZoom', 'Zoom level');

		$fields->addFieldToTab('Root.Content.Main', new HTMLEditorField('FAQ'));

		$fields->addFieldToTab('Root.Behaviour', new CheckboxField('EnableAutoGlossary', 'Enable Auto-Glossary'));
		$fields->addFieldToTab('Root.Behaviour', new CheckboxField('EnableManualGlossaryCache', 'Enable manual edits to the glossary cache. (Prevents auto-updating of this glossary cache.)'));
		$fields->addFieldToTab('Root.Behaviour', new TextareaField('GlossaryCache', 'Glossary Cache - DO NOT EDIT UNLESS YOU KNOW WHAT YOU ARE DOING!'));
		
		return $fields;
	}

	public function removeGlossaryWord($glossaryWord) {
		if (!$this->EnableAutoGlossary || $this->EnableManualGlossaryCache) {
			return false;
		}
		
		$glossaryPage = DataObject::get_one('GlossaryPage');
		
		if (!$glossaryPage || !$glossaryPage->exists()) {
			return false;
		}
		
		$stemmer = new Stemmer();
		$html = new simple_html_dom();
		$html->load($this->GlossaryCache);
		
		$text = $html->find('p text');
		
		foreach ($text as $t) {
			if (strcmp($t->parent()->tag, 'a') == 0 && !empty($t->parent()->class) && strcmp($t->parent()->class, 'glossarylink') == 0 && strlen(trim($t->plaintext)) != 0) {
				$word = $stemmer->stem($t->plaintext);
					
				if (strcmp($word, $glossaryWord->WordStem) == 0) {
					$t->parent()->outertext = $t->plaintext;
				}
			}
		}
		
		$this->GlossaryCache = $html->save();
		$this->rebuildGlossaryCache = false;
	}
	
	public function addGlossaryWord($glossaryWord) {
		if (!$this->EnableAutoGlossary || $this->EnableManualGlossaryCache) {
			return false;
		}
		
		$glossaryPage = DataObject::get_one('GlossaryPage');
		
		if (!$glossaryPage || !$glossaryPage->exists()) {
			return false;
		}
		
		$exclusions = DataObject::get('GlossaryExclusion');
		
		if (!$exclusions || !$exclusions->exists()) {
			$exclusions = array();
		}
		
		$stemmer = new Stemmer();
		$html = new simple_html_dom();
		$originalCache = (!empty($this->GlossaryCache)) ? $this->GlossaryCache : $this->Content;
		
		$html->load($originalCache);
		
		$text = $html->find('p text');
		
		foreach ($text as $t) {
			if (strcmp($t->parent()->tag, 'a') != 0) {
				$final = '';
				$words = explode(' ', $t->outertext);
				
				foreach ($words as $rawWord) {
					foreach ($exclusions as $exclusion) {
						if (strcmp(trim($rawWord), $exclusion) == 0) {
							continue;
						}
					}
					
					$word = $stemmer->stem($rawWord);
					
					if (strlen(trim($rawWord)) != 0 && strcmp($word, $glossaryWord->WordStem) == 0) {
						$final .= ' <a class="glossarylink" target="_blank" href="'.$glossaryPage->Link().'#'.strtolower($glossaryWord->CleanWord).'">'.$rawWord.'</a>';
					} else {
						$final .= ' '.$rawWord;
					}
				}
				
				$t->outertext = substr($final, 1);
			}
		}
		
		$this->GlossaryCache = $html->save();
		$this->rebuildGlossaryCache = false;
	}
	
	public function onBeforeWrite() {
		parent::onBeforeWrite();
		
		if ($this->rebuildGlossaryCache) {
			$this->Content = str_replace('&nbsp;', ' ', $this->Content);
			
			if (!$this->EnableAutoGlossary || $this->EnableManualGlossaryCache) {
				return false;
			}
			
			$glossary = DataObject::get('GlossaryWord');
			$glossaryPage = DataObject::get_one('GlossaryPage');
			
			if (!$glossary || !$glossary->exists() || !$glossaryPage || !$glossaryPage->exists()) {
				return false;
			}
			
			$exclusions = DataObject::get('GlossaryExclusion');
		
			if (!$exclusions || !$exclusions->exists()) {
				$exclusions = array();
			}
			
			$stemmer = new Stemmer();
			$html = new simple_html_dom();
			$html->load($this->Content);
			
			$text = $html->find('p text');
			
			foreach ($text as $t) {
				if (strcmp($t->parent()->tag, 'a') != 0) {
					$final = '';
					$words = explode(' ', $t->outertext);
					
					foreach ($words as $rawWord) {
						foreach ($exclusions as $exclusion) {
							if (strcmp(trim($rawWord), $exclusion) == 0) {
								continue;
							}
						}
						
						$word = $stemmer->stem($rawWord);
						$foundMatch = false;
						
						foreach ($glossary as $glossaryWord) {
							if (strcmp($glossaryWord->WordStem, '#INVALID') != 0 && strlen(trim($rawWord)) != 0 && strcmp($word, $glossaryWord->WordStem) == 0) {
								$final .= ' <a class="glossarylink" target="_blank" href="'.$glossaryPage->Link().'#'.strtolower($glossaryWord->CleanWord).'">'.$rawWord.'</a>';
								$foundMatch = true;
								break;
							}
						}
						
						if (!$foundMatch) {
							$final .= ' '.$rawWord;
						}
					}
					
					$t->outertext = substr($final, 1);
				}
			}
			
			$this->GlossaryCache = $html->save();
		}
	}

}

class MapPage_Controller extends Page_Controller {
	private $ajax = true;
	
	function init() {
		if (Director::is_ajax()) {
			$this->ajax = true;
		}
		parent::init();
		if ($this->GMapsCoords){
			$MapCenter = $this->GMapsCoords;
		} else {
			$MapCenter = "0,0";
		}
		if ($this->DefaultZoom){
			$DefaultZoom = $this->DefaultZoom;
		} else {
			$DefaultZoom = "6";
		}
		Requirements::customScript(<<<JS
var center = new google.maps.LatLng($MapCenter);
var defaultZoom = $DefaultZoom;
JS
);
	}
	
	function getPolygons() {
		if (!$this->ajax) return false;
		$rawList = DataObject::get('MapPolygonPage','ParentID = '.$this->ID);
		$polygonList = array();
		
		if ($rawList && $rawList->exists()) {
			foreach ($rawList as $polygon) {
				$temp = array();
				
				// ID, title, array(center lat, center lng), array(lat1, lng1), array(lat2, lng2) ...
				// For Services_JSON compatibility.
				$temp[] = $polygon->ID;
				$temp[] = $polygon->Title;
				
				$rawCoords = explode(';', $polygon->GMapsCoords);
				foreach ($rawCoords as $rawVertex) {
					$vertex = explode(',', $rawVertex);
					$temp[] = array($vertex[0], $vertex[1]);
				}
				
				$polygonList[] = $temp;
			}
		}
		
		return Convert::raw2json($polygonList);
	}
	
	function getMarkers() {
		if (!$this->ajax) return false;
		$rawList = DataObject::get('MapPointPage','ParentID = '.$this->ID);
		$markerList = array();
		
		if ($rawList && $rawList->exists()) {
			foreach ($rawList as $marker) {
				$temp = array();
				
				// ID, array(lat, lng), title
				// For Services_JSON compatibility.
				$temp[] = $marker->ID;
				
				$rawCoords = explode(',', $marker->GMapsCoords);
				$temp[] = array($rawCoords[0], $rawCoords[1]);
				
				$temp[] = $marker->Title;
				
				$markerList[] = $temp;
			}
		}
		
		return Convert::raw2json($markerList);
	}
	
	function getMapContent($action) {
		if (!$this->ajax) return false;
		$params = Director::urlParams();
		$action = null;
		foreach ($params as $param){
			if ($param) {
				$action = $param;
			}
		}
		if ($action == 'FAQ'){
			return "<h1>Frequently Asked Questions About ".$this->Title."</h1>".$this->FAQ;
		}
		$ajaxData = explode('-', Convert::raw2sql($action));
		$entry = DataObject::get_by_id('Map'.$ajaxData[0].'Page', $ajaxData[1]);
		
		if ($entry && $entry->exists()) {
			return $entry->renderWith('MapContent');
		} else {
			return '<p>ERROR: No Content Found</p>';
		}
	}

	function Content() {
		if (($this->EnableAutoGlossary && !empty($this->GlossaryCache)) || $this->EnableManualGlossaryCache) {
			return $this->GlossaryCache;
		} else {
			return $this->Content;
		}
	}
	function PointsList(){
		return DataObject::get('MapPointPage','ParentID = '.$this->ID);
	}
	
	function PolygonsList(){
		return DataObject::get('MapPolygonPage','ParentID = '.$this->ID);
	}
}
