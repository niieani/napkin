<?php

// http://codeaid.net/php/calculate-script-execution-time-%28php-class%29

namespace Tools;

class Timer
{
    // command constants
	const CMD_START = 'start';
	const CMD_STOP = 'end';

	// return format constants
	const SECONDS = 0;
	const MILLISECONDS = 1;
	const MICROSECONDS = 2;

	// number of microseconds in a second
	const USECDIV = 1000000;


	/**
	 * Stores current state of the timer
	 *
	 * @var boolean
	 */
	public $_running = false;

	/**
	 * Contains the queue of times
	 *
	 * @var array
	 */
	private $_queue = array();


	/**
	 * Start the timer
	 *
	 * @return void
	 */
	public function start()
	{
		// push current time
		$this->_pushTime(self::CMD_START);
	}


	/**
	 * Stop the timer
	 *
	 * @return void
	 */
	public function stop()
	{
		// push current time
		$this->_pushTime(self::CMD_STOP);
	}


	/**
	 * Reset contents of the queue
	 *
	 * @return void
	 */
	public function reset()
	{
		// reset the queue
		$this->_queue = array();
	}


	/**
	 * Add a time entry to the queue
	 *
	 * @return void
	 */
	private function _pushTime($cmd)
	{
		// extract seconds and microseconds
		$mt = microtime();
		list($usec, $sec) = explode(' ', $mt);

		// typecast them to the required types
		$sec = (int) $sec;
		$usec = (float) $usec;
		$usec = (int) ($usec * self::USECDIV);

		// set current running state depending on the command
		if ($cmd == self::CMD_START) {
			// check if the timer has already been started
			if ($this->_running === true) {
				trigger_error('Timer has already been started', E_USER_NOTICE);
				return;
			}

			// set current state
			$this->_running = true;

		} else if ($cmd == self::CMD_STOP) {
			// check if the timer is already stopped
			if ($this->_running === false) {
				trigger_error('Timer has already been stopped/paused or has not yet been started', E_USER_NOTICE);
				return;
			}

			// set current state
			$this->_running = false;

		} else {
			// fail execution of the script
			trigger_error('Invalid command specified', E_USER_ERROR);
			return;
		}

		// create the array
		$time = array(
			$cmd => array(
				'sec'	=> $sec,
				'usec'	=> $usec,
			),
		);

		// add a time entry depending on the command
		if ($cmd == self::CMD_START) {
			array_push($this->_queue, $time);

		} else if ($cmd == self::CMD_STOP) {
			$count = count($this->_queue);
			$array =& $this->_queue[$count - 1];
			$array = array_merge($array, $time);
		}
	}


	/**
	 * Get time of execution from all queue entries
	 *
	 * @return int|float
	 */
	public function get($format = self::SECONDS)
	{
		// stop timer if it is still running
		if ($this->_running === true) {
			trigger_error('Forcing timer to stop', E_USER_NOTICE);
			$this->stop();
		}

		// reset all values
		$sec = 0;
		$usec = 0;

		// loop through each time entry
		foreach ($this->_queue as $time) {
			// start and end times
			$start = $time[self::CMD_START];
			$end = $time[self::CMD_STOP];

			// calculate difference between start and end seconds
			$sec_diff = $end['sec'] - $start['sec'];

			// if starting and finishing seconds are the same
			if ($sec_diff === 0) {
				// only add the microseconds difference
				$usec += ($end['usec'] - $start['usec']);

			} else {
				// add the difference in seconds (compensate for microseconds)
				$sec += $sec_diff - 1;

				// add the difference time between start and end microseconds
				$usec += (self::USECDIV - $start['usec']) + $end['usec'];
			}
		}

		if ($usec > self::USECDIV) {
			// move the full second microseconds to the seconds' part
			$sec += (int) floor($usec / self::USECDIV);

			// keep only the microseconds that are over the self::USECDIV
			$usec = $usec % self::USECDIV;
		}

		switch ($format) {
			case self::MICROSECONDS:
				return ($sec * self::USECDIV) + $usec;

			case self::MILLISECONDS:
				return ($sec * 1000) + (int) round($usec / 1000, 0);

			case self::SECONDS:
			default:
				return (float) $sec + (float) ($usec / self::USECDIV);
		}
	}

}

// static version
class TimerStatic
{
 
    // command constants
    const CMD_START = 'start';
    const CMD_STOP = 'end';
 
    // return format constants
    const SECONDS = 0;
    const MILLISECONDS = 1;
    const MICROSECONDS = 2;
 
    // number of microseconds in a second
    const USECDIV = 1000000;
 
 
    /**
     * Stores current state of the timer
     *
     * @var boolean
     */
    private static $_running = false;
 
    /**
     * Contains the queue of times
     *
     * @var array
     */
    private static $_queue = array();
 
 
    /**
     * Start the timer
     *
     * @return void
     */
    public static function start()
    {
        // push current time
        self::_pushTime(self::CMD_START);
    }
 
 
    /**
     * Stop the timer
     *
     * @return void
     */
    public static function stop()
    {
        // push current time
        self::_pushTime(self::CMD_STOP);
    }
 
 
    /**
     * Reset contents of the queue
     *
     * @return void
     */
    public static function reset()
    {
        // reset the queue
        self::$_queue = array();
    }
 
 
    /**
     * Add a time entry to the queue
     *
     * @return void
     */
    private static function _pushTime($cmd)
    {
        // extract seconds and microseconds
        $mt = microtime();
        list($usec, $sec) = explode(' ', $mt);
 
        // typecast them to the required types
        $sec = (int) $sec;
        $usec = (float) $usec;
        $usec = (int) ($usec * self::USECDIV);
 
        // set current running state depending on the command
        if ($cmd == self::CMD_START) {
            // check if the timer has already been started
            if (self::$_running === true) {
                trigger_error('Timer has already been started', E_USER_NOTICE);
                return;
            }
 
            // set current state
            self::$_running = true;
 
        } else if ($cmd == self::CMD_STOP) {
            // check if the timer is already stopped
            if (self::$_running === false) {
                trigger_error('Timer has already been stopped/paused or has not yet been started', E_USER_NOTICE);
                return;
            }
 
            // set current state
            self::$_running = false;
 
        } else {
            // fail execution of the script
            trigger_error('Invalid command specified', E_USER_ERROR);
            return;
        }
 
        // create the array
        $time = array(
            $cmd => array(
                'sec'   => $sec,
                'usec'  => $usec,
            ),
        );
 
        // add a time entry depending on the command
        if ($cmd == self::CMD_START) {
            array_push(self::$_queue, $time);
 
        } else if ($cmd == self::CMD_STOP) {
            $count = count(self::$_queue);
            $array =& self::$_queue[$count - 1];
            $array = array_merge($array, $time);
        }
    }
 
 
    /**
     * Get time of execution from all queue entries
     *
     * @return int|float
     */
    public static function get($format = self::SECONDS)
    {
        // stop timer if it is still running
        if (self::$_running === true) {
            trigger_error('Forcing timer to stop', E_USER_NOTICE);
            self::stop();
        }
 
        // reset all values
        $sec = 0;
        $usec = 0;
 
        // loop through each time entry
        foreach (self::$_queue as $time) {
            // start and end times
            $start = $time[self::CMD_START];
            $end = $time[self::CMD_STOP];
 
            // calculate difference between start and end seconds
            $sec_diff = $end['sec'] - $start['sec'];
 
            // if starting and finishing seconds are the same
            if ($sec_diff === 0) {
                // only add the microseconds difference
                $usec += ($end['usec'] - $start['usec']);
 
            } else {
                // add the difference in seconds (compensate for microseconds)
                $sec += $sec_diff - 1;
 
                // add the difference time between start and end microseconds
                $usec += (self::USECDIV - $start['usec']) + $end['usec'];
            }
        }
 
        if ($usec > self::USECDIV) {
            // move the full second microseconds to the seconds' part
            $sec += (int) floor($usec / self::USECDIV);
 
            // keep only the microseconds that are over the self::USECDIV
            $usec = $usec % self::USECDIV;
        }
 
        switch ($format) {
            case self::MICROSECONDS:
                return ($sec * self::USECDIV) + $usec;
 
            case self::MILLISECONDS:
                return ($sec * 1000) + (int) round($usec / 1000, 0);
 
            case self::SECONDS:
            default:
                return (float) $sec + (float) ($usec / self::USECDIV);
        }
    }

}