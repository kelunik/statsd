<?php

namespace Kelunik\StatsD;

class StatsD {
    private $socket;
    private $server;
    private $port;
    private $timers;

    public function __construct(string $server, int $port) {
        $this->socket = \socket_create(\AF_INET, \SOCK_DGRAM, \SOL_UDP);
        $this->server = $server;
        $this->port = $port;
    }

    public function escape(string $label) {
        return str_replace([".", ":", "|"], "-", $label);
    }

    public function increment(string $label) {
        $message = "kvk.view-title.{$label}:1|c";
        socket_sendto($this->socket, $message, strlen($message), 0, $this->server, $this->port);
    }

    public function timing(string $label, float $ms) {
        $message = "kvk.view-title.{$label}:{$ms}|ms";
        socket_sendto($this->socket, $message, strlen($message), 0, $this->server, $this->port);
    }

    public function startTimer(string $label) {
        $this->timers[$label] = microtime(1);
    }

    public function stopTimer(string $label) {
        if (!isset($this->timers[$label])) {
            throw new \LogicException("Can't stop a non-existent timer.");
        }

        $time = (microtime(1) - $this->timers[$label]) * 1000;
        $this->timing($label, $time);

        unset($this->timers[$label]);
    }

	public function __destruct() {
		\socket_close($this->socket);
	}
}
