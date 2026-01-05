<?php
declare(strict_types=1);

namespace Spy\Package\MonologLogger;

use Monolog\Handler\AbstractHandler;
use Monolog\Handler\FingersCrossed\ActivationStrategyInterface;
use Monolog\Handler\HandlerInterface;
use Monolog\Handler\ProcessableHandlerInterface;
use Monolog\Handler\ProcessableHandlerTrait;
use Monolog\Level;
use Monolog\LogRecord;
use Spy\Package\CentralizedLoggerData\CentralizedLoggerInterface;

/**
 * Class CentralizedMonologHandler
 *
 * @package Spy\Package\MonologLogger
 */
class CentralizedMonologHandler extends AbstractHandler implements ProcessableHandlerInterface
{
	use ProcessableHandlerTrait;

	public function __construct(
		private readonly CentralizedLoggerInterface $oCentralizeLogger,
		Level                                       $level = Level::Debug,
		bool                                        $bubble = true,
		private readonly bool                       $bAllowBufferingInCentralizedLogger = false,
	)
	{
		parent::__construct($level, $bubble);
	}

	public function handleBatch(array $records): void
	{
		$arrLogData = array_map(
			fn(LogRecord $oRecord) => new GenericMonologLogData(
				$oRecord,
				bAllowBuffering: $this->bAllowBufferingInCentralizedLogger,
			),
			$records
		);

		$this->oCentralizeLogger->logMultipleData(...$arrLogData);
	}

	public function handle(LogRecord $record): bool
	{
		/** @noinspection PhpVariableNamingConventionInspection */
		$record = $this->processRecord($record);

		$this->oCentralizeLogger->logData(
			new GenericMonologLogData(
				$record,
				bAllowBuffering: $this->bAllowBufferingInCentralizedLogger,
			)
		);

		return !$this->bubble;
	}

	/**
	 * Creates a CentralizedMonologHandler wrapped in a FingersCrossedHandler.
	 * The logs (above the $oMinLevel) will all be sent to the CentralizedLogger if a log actives the ActivationStrategy.
	 * If no logs active the ActivationStrategy, and $oPassThroughLevel is defined, then the logs above the $oPassThroughLevel will be sent to the
	 * CentralizedLogger on shutdown.
	 */
	public static function CreateWithFingersCrossedHandler(
		CentralizedLoggerInterface        $oCentralizedLogger,
		Level                             $oMinLevel = Level::Debug,
		Level|ActivationStrategyInterface $oActivationStrategy = Level::Warning,
		Level|null                        $oPassThroughLevel = null,
		bool                              $bAllowBufferingInCentralizedLogger = false,
	): HandlerInterface
	{
		$oHandler = new self(
			$oCentralizedLogger,
			$oMinLevel,
			bAllowBufferingInCentralizedLogger: $bAllowBufferingInCentralizedLogger,
		);

		return new FingersCrossedCentralizedMonologHandler(
			$oHandler,
			$oActivationStrategy,
			$oPassThroughLevel,
		);
	}
}
