<?php
include 'helpers/config.php';
use ActiveRecord\Binary;

class BinaryTest extends DatabaseTest
{
	public function set_up($connection_name=null)
	{
		parent::set_up($connection_name);
		$this->binary = new Binary();
	}

	private function assert_dirtifies($method /*, method params, ...*/)
	{
		$model = new Author();
		$binary = new Binary();
		$binary->attribute_of($model,'some_binary');

		$args = func_get_args();
		array_shift($args);

		call_user_func_array(array($binary, $method), $args);
		$this->assert_has_keys('some_binary', $model->dirty_attributes());
	}

	public function test_should_flag_the_attribute_dirty()
	{
		$this->assert_dirtifies('update', md5('test'));
		$this->assert_dirtifies('update', pack('H*', md5('test')));
	}
}

?>
