<?php

class ChunkFile {
	private $error;
	private $fp = null;
	private $file_path = null;
	private $curr_chunk = 0;
	private $chunks = array();

	const CHUNK_SIZE_BYTE = 16;

	public function error() {
		return $this->error;
	}

	public function __construct($file_path) {
		$this->file_path = $file_path;
	}

	private function _open() {
		if (!$this->fp) {
			if (!$fp = @fopen($this->file_path, 'c+')) {
				$this->error = "failed while opening file {$this->file_path} ";
				return false;
			}
			$this->fp = $fp;
		}
		return true;
	}

	/*
	*  get chunk position info 
	*
	*  @return     if it failed,  ChunkFile::error() will return a not-null string
	*/
	public function get($index) {
		if (!$this->_open() || !($pos = $this->get_pos($index)) || false === ($data = $this->_read_data($pos['pos'] + self::CHUNK_SIZE_BYTE, ($pos['size'] - self::CHUNK_SIZE_BYTE) > 0 ? ($pos['size'] - self::CHUNK_SIZE_BYTE) : 0))) {
			return false;
		}
		return $data;
	}

	/* read data from chunk position
	 *
	 */
	private function _read_data($pos, $size) {
		if (!$this->_open()) {
			return false;
		}
		if ($size <= 0) {	
			return null;
		}
		if (fseek($this->fp, $pos) === -1) {
			$this->error = "failed while seeking file {$this->file_path} at {$pos} for reading data";
			return false;
		}
		$data = fread($this->fp, $size);
		return $data;
	}


	public function get_pos($index) {
		if (!$this->_open()) {
			return false;
		}
		if (isset($this->chunks[$index])) {
			return $this->chunks[$index];
		}
		$pos = 0;
		for( $i = 0; $i < $index; $i ++) {
			if (isset($this->chunks[$i])) {
				$pos += $this->chunks[$i]['size'];
				continue;
			} else {
				if (fseek($this->fp, $pos) === -1) {
					$this->error = "failed while seeking file {$this->file_path} at {$pos} ";
					return false;
				}
				$size = fread($this->fp, self::CHUNK_SIZE_BYTE);
				if ($size === false) {
					$this->error = "failed while reading data from {$this->file_path} at {$pos} for [chunk size]".self::CHUNK_SIZE_BYTE." bytes";
					return false;
				}
				$size = intval($size);
				$this->chunks[$i] = array('pos' => $pos, 'size' => $size);
				if ($size == 0) {
					$this->error = "failed while reading data from {$this->file_path} for data[{$index}] ";
					return false;
				}
				$pos += $size;
			}
		}
		if (fseek($this->fp, $pos) === -1) {
			$this->error = "failed while seeking file {$this->file_path} at {$pos} for data[$index]";
			return false;
		}
		$size = fread($this->fp, self::CHUNK_SIZE_BYTE);
		if ($size === false) {
			$this->error = "failed while reading data from {$this->file_path} at {$pos} for [chunk size]".self::CHUNK_SIZE_BYTE." bytes";
			return false;
		}
		$size = intval($size);
		return $this->chunks[$i] = array('pos' => $pos, 'size'=>$size);
	}


	public function push($str) {
		$size = self::CHUNK_SIZE_BYTE + strlen($str);
		for ( $i = strlen($size) ; $i < self::CHUNK_SIZE_BYTE; $i ++) {
			$size = "0" . $size;
		}
		if ($this->fp) {
			fclose($this->fp);
			$this->fp = null;
		}
		if (file_put_contents($this->file_path, $size . $str , FILE_APPEND) === false) {
			$this->error = "fail while appending a chunk to file {$this->file_path}";
			return false;
		}
		return true;
	}

	public function chunks() {
		return $this->chunks;
	}
	
	public function __destruct() {
		if ($this->fp) {
			fclose($this->fp);
			$this->fp = null;
		}
	}
}
