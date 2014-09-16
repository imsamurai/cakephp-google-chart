<?php

/**
 * Author: imsamurai <im.samuray@gmail.com>
 * Date: 17.07.2014
 * Time: 15:33:57
 * Format: http://book.cakephp.org/2.0/en/development/testing.html
 */
App::uses('GoogleChartHelper', 'GoogleChart.View/Helper');
App::uses('View', 'View');

/**
 * GoogleChartHelperTest
 * 
 * @package GoogleChart
 * @subpackage Test
 * 
 * @property GoogleChartHelper $GoogleChart Google chart helper
 * @property View $View View
 */
class GoogleChartHelperTest extends CakeTestCase {

	/**
	 * {@inheritdoc}
	 */
	public function setUp() {
		parent::setUp();
		$this->View = new View();
		$this->GoogleChart = new GoogleChartHelper($this->View, array(
			'version' => '1.0',
			'apiUrl' => 'https://www.google.com/jsapi'
		));
	}

	/**
	 * Test load
	 * 
	 * @param array $options
	 * @param string $output
	 * @param bool $inline
	 * 
	 * @dataProvider loadProvider
	 */
	public function testLoad(array $options, $output, $inline = null) {
		if (!is_null($inline)) {
			$this->GoogleChart->settings['inline'] = $inline;
		}
		ob_start();
		$load = $this->GoogleChart->load($options);
		$echo = ob_get_clean();
		if ($inline) {
			$this->assertSame($output, $echo);
			$this->assertEmpty($this->View->fetch('script'));
		} else {
			$this->assertEmpty($echo);
			$this->assertSame($output, $this->View->fetch('script'));
		}
		$this->assertNull($load);
	}

	/**
	 * Data provider for testLoad
	 * 
	 * @return array
	 */
	public function loadProvider() {
		return array(
			//set #0
			array(
				//options
				array(),
				//output
				'<script type="text/javascript" src="https://www.google.com/jsapi"></script>' .
				'<script type="text/javascript">' .
				"\n" .
				'//<![CDATA[' .
				"\n" .
				'google.load(\'visualization\', 1.0, {"packages":["corechart","controls"]});' .
				"\n" .
				'//]]>' .
				"\n" .
				'</script>',
				null
			),
			//set #1
			array(
				//options
				array(
					'packages' => array('motionchart')
				),
				//output
				'<script type="text/javascript" src="https://www.google.com/jsapi"></script>' .
				'<script type="text/javascript">' .
				"\n" .
				'//<![CDATA[' .
				"\n" .
				'google.load(\'visualization\', 1.0, {"packages":["motionchart"]});' .
				"\n" .
				'//]]>' .
				"\n" .
				'</script>',
				null
			),
			//set #3
			array(
				//options
				array(
					'packages' => array('motionchart')
				),
				//output
				'<script type="text/javascript" src="https://www.google.com/jsapi"></script>' .
				'<script type="text/javascript">' .
				"\n" .
				'//<![CDATA[' .
				"\n" .
				'google.load(\'visualization\', 1.0, {"packages":["motionchart"]});' .
				"\n" .
				'//]]>' .
				"\n" .
				'</script>',
				true
			),
			//set #4
			array(
				//options
				array(
					'packages' => array('motionchart')
				),
				//output
				'<script type="text/javascript" src="https://www.google.com/jsapi"></script>' .
				'<script type="text/javascript">' .
				"\n" .
				'//<![CDATA[' .
				"\n" .
				'google.load(\'visualization\', 1.0, {"packages":["motionchart"]});' .
				"\n" .
				'//]]>' .
				"\n" .
				'</script>',
				false
			),
		);
	}

	/**
	 * Test convert list into data for chart
	 * 
	 * @param array $list
	 * @param array $headers
	 * @param array $output
	 * 
	 * @dataProvider dataFromListProvider
	 */
	public function testDataFromList(array $list, array $headers, array $output) {
		$data = $this->GoogleChart->dataFromList($list, $headers);
		$this->assertSame($output, $data);
	}

	/**
	 * Data provider for testDataFromList
	 * 
	 * @return array
	 */
	public function dataFromListProvider() {
		return array(
			//set #0
			array(
				//list
				array(),
				//headers
				array(),
				//output
				array()
			),
			//set #1
			array(
				//list
				array(
					'one' => 1,
					'two' => 2
				),
				//headers
				array('name', 'value'),
				//output
				array(
					array('name', 'value'),
					array(
						'one', 1
					),
					array(
						'two', 2
					)
				)
			),
			//set #2
			array(
				//list
				array(),
				//headers
				array('name', 'value', 's'),
				//output
				array()
			),
			//set #3
			array(
				//list
				array(),
				//headers
				array('name'),
				//output
				array()
			),
			//set #4
			array(
				//list
				array(),
				//headers
				array('name', 'value'),
				//output
				array()
			),
			//set #5
			array(
				//list
				array(
					array('one', 1, 'red'),
					array('two', 2, 'gray')
				),
				//headers
				array('name', 'value', '{"role": "style"}'),
				//output
				array(
					array('name', 'value', array('role' => 'style')),
					array(
						'one', 1, 'red'
					),
					array(
						'two', 2, 'gray'
					)
				)
			),
		);
	}

	/**
	 * Test create array for DataTable
	 * 
	 * @param array $list
	 * @param array $paths
	 * @param array $output
	 * 
	 * @dataProvider dataFromArrayProvider
	 */
	public function testDataFromArray(array $list, array $paths, array $output) {
		$data = $this->GoogleChart->dataFromArray($list, $paths);
		$this->assertSame($output, $data);
	}

	/**
	 * Data provider for testDataFromArray
	 * 
	 * @return array
	 */
	public function dataFromArrayProvider() {
		return array(
			//set #0
			array(
				//list
				array(),
				//paths
				array(),
				//output
				array()
			),
			//set #1
			array(
				//list
				array('blah'),
				//paths
				array(),
				//output
				array()
			),
			//set #2
			array(
				//list
				array(),
				//paths
				array('blah'),
				//output
				array()
			),
			//set #2
			array(
				//list
				array(
					array(
						'weight' => 1,
						'name' => 'one',
						'color' => 'red'
					),
					array(
						'weight' => 2,
						'name' => 'two',
						'color' => 'green'
					),
					array(
						'weight' => 3,
						'name' => 'three',
						'color' => 'blue'
					),
				),
				//paths
				array(
					'name' => '{n}.name',
					'weight' => '{n}.weight',
					'{"role": "style"}' => '{n}.color',
				),
				//output
				array(
					'cols' => array(
						0 => array(
							'id' => 'name',
							'label' => 'name',
							'type' => 'string'
						),
						1 => array(
							'id' => 'weight',
							'label' => 'weight',
							'type' => 'number'
						),
						2 => array(
							'p' => array('role' => 'style'),
							'type' => 'string'
						)
					),
					'rows' => array(
						0 => array(
							'c' => array(
								0 => array(
									'v' => 'one',
									'f' => 'one'
								),
								1 => array(
									'v' => 1,
									'f' => '1'
								),
								2 => array(
									'v' => 'red',
									'f' => 'red'
								)
							)
						),
						1 => array(
							'c' => array(
								0 => array(
									'v' => 'two',
									'f' => 'two'
								),
								1 => array(
									'v' => 2,
									'f' => '2'
								),
								2 => array(
									'v' => 'green',
									'f' => 'green'
								)
							)
						),
						2 => array(
							'c' => array(
								0 => array(
									'v' => 'three',
									'f' => 'three'
								),
								1 => array(
									'v' => 3,
									'f' => '3'
								),
								2 => array(
									'v' => 'blue',
									'f' => 'blue'
								)
							)
						)
					)
				)
			),
			//set #3
			array(
				//list
				array(
					array(
						'weight' => 1,
						'one_weight' => 1,
						'two_weight' => 2,
						'three_weight' => 3333333333,
						'name' => 'one'
					),
					array(
						'weight' => 11,
						'one_weight' => 11,
						'two_weight' => 22,
						'three_weight' => 3333333333,
						'name' => 'two'
					),
					array(
						'weight' => 111,
						'one_weight' => 111,
						'two_weight' => 222,
						'three_weight' => 3333333333,
						'name' => 'three'
					),
				),
				//paths
				array(
					'name' => '{n}.name',
					'weight' => '{n}.weight',
					array(
						'names' => '{n}.name',
						'values' => array(
							'v' => '{n}.%s_weight',
							'f' => '{n}.name'
						)
					)
				),
				//output
				array(
					'cols' => array(
						0 => array(
							'id' => 'name',
							'label' => 'name',
							'type' => 'string'
						),
						1 => array(
							'id' => 'weight',
							'label' => 'weight',
							'type' => 'number'
						),
						2 => array(
							'id' => 'one',
							'label' => 'one',
							'type' => 'number'
						),
						3 => array(
							'id' => 'two',
							'label' => 'two',
							'type' => 'number'
						),
						4 => array(
							'id' => 'three',
							'label' => 'three',
							'type' => 'datetime'
						)
					),
					'rows' => array(
						0 => array(
							'c' => array(
								0 => array(
									'v' => 'one',
									'f' => 'one'
								),
								1 => array(
									'v' => 1,
									'f' => '1'
								),
								2 => array(
									'v' => 1,
									'f' => 'one'
								),
								3 => array(
									'v' => 2,
									'f' => 'one'
								),
								4 => array(
									'v' => 3333333333,
									'f' => 'one'
								)
							)
						),
						1 => array(
							'c' => array(
								0 => array(
									'v' => 'two',
									'f' => 'two'
								),
								1 => array(
									'v' => 11,
									'f' => '11'
								),
								2 => array(
									'v' => 11,
									'f' => 'two'
								),
								3 => array(
									'v' => 22,
									'f' => 'two'
								),
								4 => array(
									'v' => 3333333333,
									'f' => 'two'
								)
							)
						),
						2 => array(
							'c' => array(
								0 => array(
									'v' => 'three',
									'f' => 'three'
								),
								1 => array(
									'v' => 111,
									'f' => '111'
								),
								2 => array(
									'v' => 111,
									'f' => 'three'
								),
								3 => array(
									'v' => 222,
									'f' => 'three'
								),
								4 => array(
									'v' => 3333333333,
									'f' => 'three'
								)
							)
						)
					)
				)
			),
		);
	}

	/**
	 * Test draw
	 * 
	 * @param string $type
	 * @param array $data
	 * @param array $options
	 * @param array $events
	 * @param string $output
	 * 
	 * @dataProvider drawProvider
	 */
	public function testDraw($type, array $data, array $options, array $events, $output) {
		$holder = $this->GoogleChart->draw($type, $data, $options, $events);

		if (!empty($options['div'])) {
			foreach ($options['div'] as $name => $value) {
				$this->assertRegExp('#^<div\s.*' . $name . '=\"' . $value . '\".*<\/div>$#', $holder);
			}
		}

		$pattern = '#^<div\s.*id=\"([^"]*)\".*<\/div>$#';
		$this->assertRegExp($pattern, $holder);
		preg_match($pattern, $holder, $matches);
		$id = $matches[1];
		$this->assertSame(sprintf($output, $id), $this->View->fetch('script'));
	}

	/**
	 * Data provider for testDraw
	 */
	public function drawProvider() {
		return array(
			//set #0
			array(
				//type
				'BarChart',
				//data
				array(
					array('name', 'value'),
					array(
						'one', 1
					),
					array(
						'two', 2
					)
				),
				//options
				array(),
				//events
				array(),
				//output
				'<script type="text/javascript">' .
				"\n" .
				'//<![CDATA[' .
				"\n" .
				'setTimeout(' .
				'function(){' .
				'$(document).ready(' .
				'function () {' .
				'var data = new google.visualization.arrayToDataTable([["name","value"],["one",1],["two",2]]);' .
				'var chart = new google.visualization.BarChart(document.getElementById("%s"));' .
				'chart.draw(data, []);' .
				'}' .
				');' .
				'}, 100);' .
				"\n" .
				'//]]>' .
				"\n" .
				'</script>'
			),
			//set #1
			array(
				//type
				'BarChart',
				//data
				array(
					'cols' => array(
						0 => array(
							'id' => 'name',
							'label' => 'name',
							'type' => 'string'
						),
						1 => array(
							'id' => 'weight',
							'label' => 'weight',
							'type' => 'number'
						),
						2 => array(
							'id' => 'one',
							'label' => 'one',
							'type' => 'number'
						),
						3 => array(
							'id' => 'two',
							'label' => 'two',
							'type' => 'number'
						),
						4 => array(
							'id' => 'three',
							'label' => 'three',
							'type' => 'datetime'
						)
					),
					'rows' => array(
						0 => array(
							'c' => array(
								0 => array(
									'v' => 'one',
									'f' => 'one'
								),
								1 => array(
									'v' => 1,
									'f' => '1'
								),
								2 => array(
									'v' => 1,
									'f' => 'one'
								),
								3 => array(
									'v' => 2,
									'f' => 'one'
								),
								4 => array(
									'v' => 3333333333,
									'f' => 'one'
								)
							)
						),
						1 => array(
							'c' => array(
								0 => array(
									'v' => 'two',
									'f' => 'two'
								),
								1 => array(
									'v' => 11,
									'f' => '11'
								),
								2 => array(
									'v' => 11,
									'f' => 'two'
								),
								3 => array(
									'v' => 22,
									'f' => 'two'
								),
								4 => array(
									'v' => 3333333333,
									'f' => 'two'
								)
							)
						),
						2 => array(
							'c' => array(
								0 => array(
									'v' => 'three',
									'f' => 'three'
								),
								1 => array(
									'v' => 111,
									'f' => '111'
								),
								2 => array(
									'v' => 111,
									'f' => 'three'
								),
								3 => array(
									'v' => 222,
									'f' => 'three'
								),
								4 => array(
									'v' => 3333333333,
									'f' => 'three'
								)
							)
						)
					)
				),
				//options
				array(),
				//events
				array(),
				//output
				'<script type="text/javascript">' .
				"\n" .
				'//<![CDATA[' .
				"\n" .
				'setTimeout(' .
				'function(){' .
				'$(document).ready(' .
				'function () {' .
				'var data = new google.visualization.DataTable({"cols":[{"id":"name","label":"name","type":"string"},{"id":"weight","label":"weight","type":"number"},{"id":"one","label":"one","type":"number"},{"id":"two","label":"two","type":"number"},{"id":"three","label":"three","type":"datetime"}],"rows":[{"c":[{"v":"one","f":"one"},{"v":1,"f":"1"},{"v":1,"f":"one"},{"v":2,"f":"one"},{"v":new Date(3333333333000),"f":"one"}]},{"c":[{"v":"two","f":"two"},{"v":11,"f":"11"},{"v":11,"f":"two"},{"v":22,"f":"two"},{"v":new Date(3333333333000),"f":"two"}]},{"c":[{"v":"three","f":"three"},{"v":111,"f":"111"},{"v":111,"f":"three"},{"v":222,"f":"three"},{"v":new Date(3333333333000),"f":"three"}]}]});' .
				'var chart = new google.visualization.BarChart(document.getElementById("%s"));' .
				'chart.draw(data, []);' .
				'}' .
				');' .
				'}, 100);' .
				"\n" .
				'//]]>' .
				"\n" .
				'</script>'
			),
			//set #2
			array(
				//type
				'BarChart',
				//data
				array(
					array('name', 'value'),
					array(
						'one', 1
					),
					array(
						'two', 2
					)
				),
				//options
				array(
					'width' => 400,
					'height' => 240,
					'title' => 'Toppings I Like On My Pizza',
					'colors' => array('#e0440e', '#e6693e', '#ec8f6e', '#f3b49f', '#f6c7b6')
				),
				//events
				array(),
				//output
				'<script type="text/javascript">' .
				"\n" .
				'//<![CDATA[' .
				"\n" .
				'setTimeout(' .
				'function(){' .
				'$(document).ready(' .
				'function () {' .
				'var data = new google.visualization.arrayToDataTable([["name","value"],["one",1],["two",2]]);' .
				'var chart = new google.visualization.BarChart(document.getElementById("%s"));' .
				'chart.draw(data, {"width":400,"height":240,"title":"Toppings I Like On My Pizza","colors":["#e0440e","#e6693e","#ec8f6e","#f3b49f","#f6c7b6"]});' .
				'}' .
				');' .
				'}, 100);' .
				"\n" .
				'//]]>' .
				"\n" .
				'</script>'
			),
			//set #3
			array(
				//type
				'BarChart',
				//data
				array(
					array('name', 'value'),
					array(
						'one', 1
					),
					array(
						'two', 2
					)
				),
				//options
				array(
					'div' => array(
						'width' => 200,
						'height' => 300,
					)
				),
				//events
				array(
					'select' => 'function() { alert(123); }',
					'click' => 'function() { alert(321); }',
				),
				//output
				'<script type="text/javascript">' .
				"\n" .
				'//<![CDATA[' .
				"\n" .
				'setTimeout(' .
				'function(){' .
				'$(document).ready(' .
				'function () {' .
				'var data = new google.visualization.arrayToDataTable([["name","value"],["one",1],["two",2]]);' .
				'var chart = new google.visualization.BarChart(document.getElementById("%s"));' .
				'google.visualization.events.addListener(chart, \'select\', function() { alert(123); });' .
				'google.visualization.events.addListener(chart, \'click\', function() { alert(321); });' .
				'chart.draw(data, []);' .
				'}' .
				');' .
				'}, 100);' .
				"\n" .
				'//]]>' .
				"\n" .
				'</script>'
			),
		);
	}

}
