<?php

namespace A2billing\PhpAgi;

use PhpAgi\AGI as BaseAGI;

class Agi extends BaseAGI
{
    private bool $play_audio = true;

    public function set_play_audio($bool)
    {
        $this->play_audio = (bool)$bool;
    }

    public function say_digits(int $digits, string $escape_digits = ''): array
    {
        return $this->play_audio ? parent::say_digits($digits, $escape_digits) : [];
    }

    public function say_number(int $number, string $escape_digits = ''): array
    {
        return $this->play_audio ? parent::say_number($number, $escape_digits) : [];
    }

    public function say_phonetic(string $text, string $escape_digits = ''): array
    {
        return $this->play_audio ? parent::say_phonetic($text, $escape_digits) : [];
    }

    public function say_time(int $time = null, string $escape_digits = ''): array
    {
        return $this->play_audio ? parent::say_time($time, $escape_digits) : [];
    }

    public function stream_file(string $filename, string $escape_digits = '', $offset = 0): array
    {
        return $this->play_audio ? parent::stream_file($filename, $escape_digits, $offset) : [];
    }

    public function espeak(string $text, string $escape_digits = '', int $frequency = 8000, string $voice = null)
    {
        $voice ??= "f2";

        $text = trim($text);
        if ($text == '') {

            return true;
        }

        $fname = $this->config['phpagi']['tempdir'] . DIRECTORY_SEPARATOR . 'espeak_' . md5($text);

        // create wave file
        if (!file_exists("$fname.wav")) {
            file_put_contents("$fname.txt", $text);
            $cmd = escapeshellarg($this->config['espeak']['espeak']);
            $cmd .= " -v " . escapeshellarg($voice);
            $cmd .= " -f " . escapeshellarg("$fname.txt");
            $cmd .= " --stdout ";

            $cmd .= " | ";

            $cmd .= escapeshellarg($this->config['sox']['sox']);
            $cmd .= " - ";
            $cmd .= " -r " . escapeshellarg($frequency);
            $cmd .= " -c1 " . escapeshellarg("$fname.wav");

            shell_exec($cmd);
        }

        // stream it
        $ret = $this->stream_file($fname, $escape_digits);

        // clean up old files
        $delete = time() - 2592000; // 1 month
        $this->clearTemp('espeak_*', $delete);

        return $ret;
    }
}
