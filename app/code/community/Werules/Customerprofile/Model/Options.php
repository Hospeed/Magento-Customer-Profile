<?php
class Werules_Customerprofile_Model_Options
{
	/**
	 * Provide available options as a value/label array
	 *
	 * @return array
	 */
	public function toOptionArray()
	{
		return array(
			array('value'=>0, 'label'=>'Disable'),
			array('value'=>1, 'label'=>'Enable')
		);
	}
}