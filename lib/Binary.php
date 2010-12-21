<?php
/**
 * @package ActiveRecord
 */
namespace ActiveRecord;

/**
 * An interface of attributes that link them 
 * 
 * @package ActiveRecord
 */
interface InterfaceAttribute
{
	/**
	 * Tell the attribute which model to dirty when its inner value changed
	 * 
	 * @param $model object the Model object
	 * @param $attribute_name string name of the attribute
	 */
	public function attribute_of($model, $attribute_name);
}

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
class Binary implements InterfaceAttribute
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
		if ($name == 'hex')
			return $this->getHex();
		if ($name == 'bin')
			return $this->getBin();
	}

	public function __set($name, $value) {
		if ($name == 'hex')
			$this->setHex($value);
		elseif ($name == 'bin')
			$this->setBin($value);
	}

	public function format($format=null) {
		switch ($format) {
			case 'bin':
				return $this->getBin();
			case 'hex':
			default:
				return $this->getHex();
		}
	}

	public function update($value) {
		if ($value !== $this->bin && $value !== $this->hex) {
			$this->_update($value);
			$this->flag_dirty();
		}
	}

	public function setHex($value) {
		if ($this->hex != $value) {
			$this->hex = $value;
			$this->bin = null;
			$this->flag_dirty();
		}
	}
	
	public function setBin($value) {
		if ($this->bin != $value) {
			$this->bin = $value;
			$this->hex = null;
			$this->flag_dirty();
		}
	}

	private function _update($value) {
		if (preg_match('/^[0-9a-zA-Z]*$/', $value)) {
			$this->hex = $value;
			$this->bin = null;
		} else {
			$this->bin = $value;
			$this->hex = null;
		}
	}

	private function getBin() {
		if (!isset($this->bin)) {
			$this->bin = pack('H*', $this->hex);
		}
		return $this->bin;
	}

	private function getHex() {
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
