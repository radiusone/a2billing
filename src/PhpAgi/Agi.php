<?php

namespace A2billing\PhpAgi;

/**
 * @method array|string get_variable($variable, $getvalue = false)
 */
class Agi extends \AGI
{
    public $play_audio = true;

    public function __construct()
    {
        parent::__construct();
    }

    public function set_play_audio($bool)
    {
        $this->play_audio = (bool)$bool;
    }

    public function say_digits($digits, $escape_digits = '')
    {
        return $this->play_audio ? parent::say_digits($digits, $escape_digits) : null;
    }

    public function say_number($number, $escape_digits = '')
    {
        return $this->play_audio ? parent::say_number($number, $escape_digits) : null;
    }

    public function say_phonetic($text, $escape_digits = '')
    {
        return $this->play_audio ? parent::say_phonetic($text, $escape_digits) : null;
    }

    public function say_time($time = null, $escape_digits = '')
    {
        return $this->play_audio ? parent::say_time($time, $escape_digits) : null;
    }

    public function stream_file($filename, $escape_digits = '', $offset = 0)
    {
        return $this->play_audio ? parent::stream_file($filename, $escape_digits, $offset) : null;
    }

    public function exec($application, $options = array())
    {
        return parent::exec($application, $options);
    }

    public function espeak($text, $escape_digits = '', $frequency = 8000, $voice = null)
    {
        if(is_null($voice)) {
            $voice = "-vf2";
        }

        $text = trim($text);
        if($text == '') {
            return true;
        }

        $hash = md5($text);
        $fname = $this->config['phpagi']['tempdir'] . DIRECTORY_SEPARATOR;
        $fname .= 'espeak_' . $hash;

        // create wave file
        if (!file_exists("$fname.wav")) {
            // write text file
            if (!file_exists("$fname.txt")) {
                $fp = fopen("$fname.txt", 'w');
                fputs($fp, $text);
                fclose($fp);
            }
            shell_exec("{$this->config['espeak']['espeak']} $voice -f $fname.txt -w $fname" . "_orig.wav ");

            shell_exec("{$this->config['sox']['sox']} $fname"."_orig.wav -r $frequency -c1 $fname.wav ");
            unlink("$fname"."_orig.wav");
        }

        // stream it
        $ret = $this->stream_file($fname, $escape_digits);

        // clean up old files
        $delete = time() - 2592000; // 1 month
        foreach(glob($this->config['phpagi']['tempdir'] . DIRECTORY_SEPARATOR . 'espeak_*') as $file) {
            if (filemtime($file) < $delete) {
                unlink($file);
            }
        }

        return $ret;
    }
}
