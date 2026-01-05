<?php
declare(strict_types=1);

namespace Spy\Package\MonologLogger;

use DateTimeInterface;
use Monolog\LogRecord;
use Spy\Package\CentralizedLoggerData\LogDataInterface;
use Spy\Package\CentralizedLoggerData\LogDataWithDateTimeInterface;
use Spy\Package\CentralizedLoggerData\LogType;

/**
 * Class GenericMonologLogData
 *
 * @package Spy\Package\MonologLogger
 */
class GenericMonologLogData implements LogDataInterface, LogDataWithDateTimeInterface
{
	public function __construct(
		private readonly LogRecord $oLogRecord,
		private readonly bool      $bAllowBuffering = false,
	)
	{
	}

	public function getType(): LogType
	{
		return LogType::GenericMonolog;
	}

	public function getData(): array
	{
		return [
			'context'    => $this->oLogRecord->context,
			'channel'    => $this->oLogRecord->channel,
			'level'      => $this->oLogRecord->level,
			'level_name' => $this->oLogRecord->level->name,
			'message'    => $this->oLogRecord->message,
			'extra'      => $this->oLogRecord->extra,
		];
	}

	public function allowBuffering(): bool
	{
		return $this->bAllowBuffering;
	}

	public function getDateTime(): DateTimeInterface
	{
		return $this->oLogRecord->datetime;
	}
}
