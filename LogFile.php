<?php

/**
 * Copyright (c) 2017 Patrick Gunsolley
 */

declare(strict_types = 1);

namespace LogAlerts;

use \Iterator;
use \Exception;

/**
 * Class LogFile
 *
 * A simple class for parsing and handling log files (or any files).
 *
 * Interface with class instance as an iterable, indexed by line number, or call
 * LogFile::readIntoArray() to parse the entire file contents into an array
 * indexed by line number.
 *
 * @package LogAlerts
 */
class LogFile implements Iterator
{
    /**
     * Exception message when the file pointer has been closed (via gc or fclose)
     */
    const CLOSED_RESOURCE_MESSAGE   = 'Unable to return line because the file pointer is closed';


    /**
     * Exception message when the file does not exist
     */
    const FILE_NOT_FOUND_MESSAGE    = 'The specified file %s does not exist or is inaccessible.';

    /**
     * The name of the file (without the full path)
     * @var
     */
    protected $name;

    /**
     * The string path to the file
     * @var
     */
    protected $path;

    /**
     * An opened file pointer to the file at LogFile::$path
     *
     * @var bool|resource
     */
    protected $resource;

    /**
     * A search pattern used for filtering each line
     *
     * @var null
     */
    protected $filterLineBy;

    /**
     * A loose representation of the current position of the file pointer
     * by line number
     *
     * @var int
     */
    protected $linePosition = 1;


    /**
     * LogFile constructor.
     * @param $path
     * @param string|null $filterLineBy
     * @throws \Exception
     */
    public function __construct(string $path, string $filterLineBy = null)
    {
        if (!file_exists($path)) {
            throw new Exception(sprintf(self::FILE_NOT_FOUND_MESSAGE, $path));
        }

        $this->name = array_pop(explode('/', $path));
        $this->path = $path;
        $this->resource = fopen($path, 'r');
        $this->filterLineBy = $filterLineBy;
    }

    /**
     * Returns the current line position
     *
     * This is not an accurate representation of the cursor position when
     * called manually. This method is used by the iterator interface.
     *
     * @return bool|int
     */
    public function key(): int
    {
        return $this->linePosition;
    }

    /**
     * Checks for the end of file line
     *
     * @return bool
     */
    public function valid(): bool
    {
        return !feof($this->resource);
    }

    /**
     * Close and reopen the file pointer at the first line
     */
    public function rewind()
    {
        $this->linePosition = 1;
        rewind($this->resource);
    }

    /**
     * Increment the line position counter
     *
     * This is called when interfacing with the object as an iterator
     *
     * Do not use directly unless you know what you're doing
     */
    public function next()
    {
        ++$this->linePosition;
    }

    /**
     * Return the line at the current position
     *
     * @return mixed
     */
    public function current(): string
    {
        $line = fgets($this->resource);
        if ($line !== false) {
            return $this->filterLine($line);
        }
        return '';
    }

    /**
     * Just close the file pointer and set LogFile::$linePosition tp -1 to indicate the file is done.
     *
     * Call this when you're done with this file to allow the gc to free resources
     */
    public function close()
    {
        $this->linePosition = -1;
        fclose($this->resource);
    }

    /**
     * Filter the current line by the search pattern in LogFile::$filterLineBy
     *
     * @param $line
     * @return mixed
     */
    protected function filterLine(string $line): string
    {
        if ($this->filterLineBy !== null) {
            if (preg_match($this->filterLineBy, $line)) {
                return $line;
            } else {
                return '';
            }
        }
        return $line;
    }

    /**
     * Just return the entire file contents as an array
     *
     * Use this for a convenient way to externally process
     * the file contents
     *
     * Warning: This will load the entire file into memory
     *
     * @return array
     * @throws Exception
     */
    public function readIntoArray(): array
    {
        if (is_resource($this->resource)) {
            $dataCache = [];
            if ($this->linePosition !== 1) {
                $this->rewind();
            }
            foreach ($this as $lineNum => $line) {
                if ($line !== '') {
                    $dataCache[] = $line;
                }
            }
            $this->rewind();
            return $dataCache;
        }
        throw new Exception(self::CLOSED_RESOURCE_MESSAGE);
    }

    /**
     * Reads the entire file directly into a string
     *
     * Use this for a quick and easy interface.
     *
     * Similar to readIntoArray, this method will load
     * the entire file into memory.
     *
     * @return string
     * @throws Exception
     */
    public function readIntoString(): string
    {
        if (is_resource($this->resource)) {
            $dataCache = '';
            if ($this->linePosition !== 1) {
                $this->rewind();
            }
            foreach ($this as $lineNum => $line) {
                $dataCache .= $line;
            }
            $this->rewind();
            return $dataCache;
        }
        throw new Exception(self::CLOSED_RESOURCE_MESSAGE);
    }
}
