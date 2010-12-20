<?php
/**
 * @package ActiveRecord
 */
namespace ActiveRecord;

/**
 * A type that handles Binary types and easy hex / bin conversion.
 *
 * All binary fields from your database will be created as instances of this class.
 *
 * Example of formatting:
 *
 * <code>
 * $password = new ActiveRecord\Binary(md5('password'));
 *
 * echo $password->format();         # 5f4dcc3b5aa765d61d8327deb882cf99
 * echo $password->format('hex');    # 5f4dcc3b5aa765d61d8327deb882cf99
 * echo $password->format('bin');    # _some gibberish_
 *
 * # __toString() uses the hex formatter
 * echo (string)$password;           # 5f4dcc3b5aa765d61d8327deb882cf99
 * </code>
 *
 *
 * @package ActiveRecord
 */
class Binary
{
	private $bin;
	private $hex;
	private $model;
	private $attribute_name;

	function __construct($value=null) {
		$this->_update($value);
	}

	public function attribute_of($model, $attribute_name)
	{
		$this->model = $model;
		$this->attribute_name = $attribute_name;
	}

	public function __get($name) {
		return $this->format($name);
	}

	public function format($format=null) {
		switch ($format) {
			case 'bin':
				return $this->get_bin();
			case 'hex':
			default:
				return $this->get_hex();
		}
	}

	public function update($value) {
		if ($value !== $this->bin && $value !== $this->hex) {
			$this->flag_dirty();
			$this->_update($value);
		}
	}

	private function _update($value) {
		if (preg_match('/^[0-9a-zA-Z]*$/', $value)) {
			$this->hex = $value;
		} else {
			$this->bin = $value;
		}
	}

	private function get_bin() {
		if (!isset($this->bin)) {
			$this->bin = pack('H*', $this->hex);
		}
		return $this->bin;
	}

	private function get_hex() {
		if (!isset($this->hex)) {
			$strings = unpack('H*', $this->bin);
			$this->hex = $strings[1];
		}
		return $this->hex;
	}

	public function __toString() {
		return $this->format();
	}

	private function flag_dirty()
	{
		if ($this->model)
			$this->model->flag_dirty($this->attribute_name);
	}
}

?>
