<?php

/**
 * Author: imsamurai <im.samuray@gmail.com>
 * Date: 13.07.2012
 * Time: 13:49:19
 * Format: http://book.cakephp.org/2.0/en/views/helpers.html
 */

App::uses('AppHelper', 'View/Helper');

/**
 * Google Chart helper
 * 
 * @package GoogleChart
 * @subpackage Helper
 * 
 * @property HtmlHelper  $Html
 * @property JqueryEngineHelper $JqueryEngine
 */
class GoogleChartHelper extends AppHelper {

	/**
	 * {@inheritdoc}
	 *
	 * @var array 
	 */
	public $helpers = array('Html', 'JqueryEngine');

	/**
	 * {@inheritdoc}
	 * 
	 * @param View $View
	 * @param array $settings
	 */
	public function __construct(View $View, $settings = array()) {
		$settings += array(
			'version' => '1.0',
			'apiUrl' => 'https://www.google.com/jsapi',
			'inline' => $View->request->is('requested') || $View->request->is('ajax')
		);
		parent::__construct($View, $settings);
	}

	/**
	 * Loads google chart visualization
	 * 
	 * @param array $options
	 */
	public function load(array $options = array()) {
		echo $this->Html->script($this->settings['apiUrl'], array(
			'inline' => $this->settings['inline'], 
			'once' => true
		));
		$options += array(
			'packages' => array(
				'corechart',
				'controls'
			)
		);
		$script = "google.load('visualization', {$this->settings['version']}, " . json_encode($options) . ");";
		echo $this->Html->scriptBlock($script, array('inline' => $this->settings['inline']));
	}

	/**
	 * Convert simple list into chart array format
	 * 
	 * @param array $list
	 * @param array $headers
	 * @return array
	 * 
	 * @throws InvalidArgumentException
	 */
	public function dataFromList(array $list, array $headers = array('Name', 'Value')) {
		if (count($list) === 0) {
			return array();
		}
		$data = array(array_map(function($header) {
			$decodedHeader = json_decode($header, true);
			return $decodedHeader ? $decodedHeader : $header;
		}, $headers));

		$keys = array_keys($list);
		if (is_integer($keys[0])) {
			return array_merge($data, $list);
		}
		
		foreach ($list as $name => $value) {
			$data[] = array($name, $value);
		}

		return $data;
	}

	/**
	 * Convert array using given paths into chart array format
	 * 
	 * Chart short names:
	 *   - c means cell
	 *   - v means value
	 *   - f means formatted value
	 * 
	 * @param array $data
	 * @param array $paths
	 * @return array
	 */
	public function dataFromArray(array $data, array $paths) {
		if (count($paths) === 0 || count($data) === 0) {
			return array();
		}
		$resultData = array(
			'cols' => array(),
			'rows' => array()
		);

		$paths2 = array();
		foreach ($paths as $pathName => $path) {
			if (is_numeric($pathName) && !empty($path['names'])) {
				$names = Hash::extract($data, $path['names']);
				foreach ($names as $name) {
					$callback = function($element) use ($name) {
						return sprintf($element, $name);
					};
					$paths2[$name] = Hash::map($path['values'], '', $callback);
				}
				unset($paths[$pathName]);
			}
		}
		$paths += $paths2;

		$dataRaw = array_combine(array_keys($paths), array_fill(0, count($paths), array()));

		foreach ($dataRaw as $type => &$one) {
			if (is_array($paths[$type])) {
				foreach ($paths[$type] as $variable => $path) {
					$part = Hash::extract($data, $path);
					$partCount = count($part);
					for ($i = 0; $i < $partCount; $i++) {
						if (!isset($one[$i])) {
							$one[$i] = array();
						}
						$one[$i][$variable] = $part[$i];
					}
				}
			} else {
				$one = Hash::extract($data, $paths[$type]);
				foreach ($one as &$value) {
					$value = array(
						'v' => $value,
						'f' => (string)$value
					);
				}
				unset($value);
			}
		}

		$headers = array_keys($dataRaw);
		foreach ($headers as $header) {
			$value = isset($dataRaw[$header][0]['v']) ? $dataRaw[$header][0]['v'] : (isset($dataRaw[$header][0]) ? $dataRaw[$header][0] : '');
			$decodedHeader = json_decode($header, true);
			if ($decodedHeader) {
				$col = array(
					'p' => $decodedHeader
				);
			} else {
				$col = array(
					'id' => $header,
					'label' => $header
				);
			}
			$resultData['cols'][] = $col + array(
				'type' => (gettype($value) !== 'string') ? (strlen((string)$value) === 10 ? 'datetime' : 'number') : 'string'
			);
		}
		
		$dataRawCount = count($dataRaw[$headers[0]]);
		for ($i = 0; $i < $dataRawCount; $i++) {
			$resultDataPart = array();
			foreach ($headers as $header) {
				$resultDataPart[] = isset($dataRaw[$header][$i]) ? $dataRaw[$header][$i] : null;
			}
			$resultData['rows'][] = array('c' => $resultDataPart);
		}

		return $resultData;
	}

	/**
	 * Draw chart
	 * 
	 * @param string $type
	 * @param array $data
	 * @param array $options
	 * @param array $events
	 * @return string
	 */
	public function draw($type, array $data, array $options = array(), array $events = array()) {
		$chartId = 'chart-' . String::uuid();

		$divOptions = array('id' => $chartId);
		if (!empty($options['div'])) {
			$divOptions = $options['div'] + $divOptions;
			$chartId = $divOptions['id'];
			unset($options['div']);
		}
		
		$isDataTable = isset($data['cols']) && isset($data['rows']);
		
		if (!$isDataTable) {
			$script = 'var data = new google.visualization.arrayToDataTable(' . $this->_encode($data) . ');';
		} else {
			$script = 'var data = new google.visualization.DataTable(' . $this->_encode($data) . ');';
		}

		$script .= 'var chart = new google.visualization.' . $type . '(document.getElementById("' . $chartId . '"));';

		foreach ($events as $eventName => $eventCallback) {
			$script .= "google.visualization.events.addListener(chart, '$eventName', $eventCallback);";
		}

		$script .= 'chart.draw(data, ' . json_encode($options) . ');';
		$scriptBlock = 'setTimeout(function(){' . $this->JqueryEngine->domReady($script) . '}, 100);';

		return $this->Html->div(null, '', $divOptions) . $this->Html->scriptBlock($scriptBlock, array(
					'inline' => $this->settings['inline']
		));
	}

	/**
	 * Encode data
	 * 
	 * @param array $data
	 * @return string
	 */
	protected function _encode(array $data) {
		return preg_replace_callback('/:([0-9]{10})/u', function ($e) {
			return ':new Date(' . ($e[1] * 1000) . ')';
		}, json_encode($data));
	}

}
