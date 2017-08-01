<?php

class CompressData
{
    const COMPRESS_PREFIX = ":\x1f\x8b";

    /**
     * @var integer
     */
    protected $_compressThreshold = 20480;

    /**
     * @var string
     */
    protected $_compressionLib;

    /**
     * @var integer
     */
    protected $_compressData = 3;

    function __construct($options = [])
    {
        if (isset($options['compression_lib'])) {
            $this->_compressionLib = $options['compression_lib'];
        } else if (function_exists('snappy_compress')) {
            $this->_compressionLib = 'snappy';
        } else {
            $this->_compressionLib = 'gzip';
        }
        $this->_compressPrefix = substr($this->_compressionLib, 0, 2) . self::COMPRESS_PREFIX;
    }

    /**
     * @param string $data
     * @param int $compressData
     * @throws Exception
     * @return string
     */
    function encodeData($data, $compressData)
    {
        if ($compressData && strlen($data) >= $this->_compressThreshold) {
            switch ($this->_compressionLib) {
                case 'snappy':
                    $data = snappy_compress($data);
                    break;
                case 'lzf':
                    $data = lzf_compress($data);
                    break;
                case 'gzip':
                    $data = gzcompress($data, $compressData);
                    break;
            }
            if (!$data) {
                throw new Exception("Could not compress cache data.");
            }
            return $this->_compressPrefix . $data;
        }
        return $data;
    }

    /**
     * @param bool|string $data
     * @return string
     */
    function decodeData($data)
    {
        if (substr($data, 2, 3) == self::COMPRESS_PREFIX) {
            switch (substr($data, 0, 2)) {
                case 'sn':
                    return snappy_uncompress(substr($data, 5));
                case 'lz':
                    return lzf_decompress(substr($data, 5));
                case 'gz':
                case 'zc':
                    return gzuncompress(substr($data, 5));
            }
        }
        return $data;
    }
}
?>