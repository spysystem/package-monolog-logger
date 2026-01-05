<?php
declare(strict_types=1);

namespace Spy\Package\MonologLogger;

use Monolog\Handler\FingersCrossed\ActivationStrategyInterface;
use Monolog\Handler\FingersCrossed\ErrorLevelActivationStrategy;
use Monolog\Handler\Handler;
use Monolog\Handler\HandlerInterface;
use Monolog\Level;
use Monolog\LogRecord;

/**
 * Class FingersCrossedCentralizedMonologHandler
 *
 * @package Spy\Package\MonologLogger
 */
class FingersCrossedCentralizedMonologHandler extends Handler
{
	private readonly ActivationStrategyInterface $oActivationStrategy;

	/**
	 * @phpstan-var list<LogRecord> $arrBuffer
	 */
	private array $arrBuffer    = [];
	private bool  $bIsActivated = false;

	public function __construct(
		private readonly HandlerInterface $oHandler,
		Level|ActivationStrategyInterface $oActivationStrategy = Level::Warning,
		private readonly Level|null       $oPassThroughLevel = null,
	)
	{
		if($oActivationStrategy instanceof Level)
		{
			$oActivationStrategy = new ErrorLevelActivationStrategy($oActivationStrategy);
		}

		$this->oActivationStrategy = $oActivationStrategy;
	}

	public function isHandling(LogRecord $record): bool
	{
		return true;
	}

	private function activate(LogRecord $oRecord): void
	{
		$this->bIsActivated = true;

		$this->oHandler->handleBatch(array_merge($this->arrBuffer, [$oRecord]));

		$this->arrBuffer = [];
	}

	public function handle(LogRecord $record): bool
	{
		if($this->bIsActivated)
		{
			$this->oHandler->handle($record);

			return false;
		}

		if($this->oActivationStrategy->isHandlerActivated($record))
		{
			$this->activate($record);
		}
		elseif($this->oPassThroughLevel?->includes($record->level) ?? false)
		{
			$this->oHandler->handle($record);
		}
		else
		{
			$this->arrBuffer[] = $record;
		}

		return false;
	}
}
