<?php

class Ratelimit {

	/**
	 * Rediska instance
	 * @var Rediska 
	 */
	protected $_rediska;

	/**
	 * Action e.g. 'page:id:view', 'hit',  etc ..
	 * @var string
	 */
	protected $_action;

	/**
	 *  is the total size of "hashring" in seconds
	 * @var int
	 */
	protected $_bucketSpan;

	/**
	 * amount of seconds each bucket represents
	 * @var int
	 */
	protected $_bucketInterval;

	/**
	 * (derived) Bucket count = Bucket span / Bucket interval
	 * @var int
	 */
	protected $_bucketCount;

	/**
	 * amount of (inactive) seconds before a subject's time buckets expire
	 * @var int
	 */
	protected $_subjectExpire;

	/**
	 * Constructor
	 *
	 * @param Rediska $client
	 * @param string $action
	 * @param int $bucketSpan
	 * @param int $bucketInterval
	 * @param int $subjectExpire
	 */
	public function __construct(Rediska $rediska, $action, $bucketSpan = 600, $bucketInterval = 5, $subjectExpire = 1200) {
		$this->_rediska = $rediska;
		$this->_action = (string) $action;
		$this->_bucketSpan = (int) $bucketSpan;
		$this->_bucketInterval = (int) $bucketInterval;
		$this->_bucketCount = (int) round($this->_bucketSpan / $this->_bucketInterval);
		$this->_subjectExpire = (int) $subjectExpire;
	}

	/**
	 * Increment the count for the specified subject.
	 *
	 * @param string $subject A unique identifier, for example a session id or an IP
	 * @return void
	 */
	public function increment($subject) {
		$bucket = $this->_getBucketName();
		$transaction = $this->_getTransaction();
		$this->_setMultiIncrementTransactionPart($transaction, $subject, $bucket);
		$transaction->execute();
	}

	/**
	 * Count the number of times the subject has performed an action in the last `$interval` seconds.
	 *
	 * @param string $subject A unique identifier, for example a session id or an IP
	 * @param int $interval Interval in seconds
	 * @return int
	 */
	public function getRateByInterval($subject, $interval) {
		$bucket = $this->_getBucketName();
		$count = (int) floor($interval / $this->bucketInterval);
		$transaction = $this->_getTransaction();
		$this->_setMulitExecGetCountPart($transaction, $subject, $bucket, $count);
		return array_sum($transaction->execute());
	}

	/**
	 * Calls the increment() and count() function using a single MULTI/EXEC block.
	 *
	 * @param string $subject A unique identifier, for example a session id or an IP
	 * @param int $interval Interval in seconds
	 * @return int
	 */
	public function incrementAndGetCountByInterval($subject, $interval) {
		$bucket = $this->_getBucketName();
		$count = (int) floor($interval / $this->_bucketInterval);
		$transaction = $this->_getTransaction();
		$this->_setMultiIncrementTransactionPart($transaction, $subject, $bucket);
		$this->_setMulitExecGetCountPart($transaction, $subject, $bucket, $count);

		return array_sum(array_slice($transaction->execute(), 4));
	}

	/**
	 * Resets the counter for the specified subject.
	 *
	 * @param string $subject A unique identifier, for example a session id or an IP
	 * @return bool
	 */
	public function reset($subject) {
		$keyName = $this->_getKeyName($subject);
		return (bool) $this->_rediska->delete($keyName);
	}

	/**
	 * Get the bucket associated with the current time.
	 *
	 * @param int $time (optional) - default is the current time (seconds since epoch)
	 * @return int bucket
	 */
	protected function _getBucketName($time = null) {
		$time = $time ? : time();
		return (int) floor(($time % $this->_bucketSpan) / $this->_bucketInterval);
	}

	/**
	 * Adds the commands needed for the increment function
	 *
	 * @param Rediska_Transaction $transaction
	 * @param string $subject A unique identifier, for example a session id or an IP
	 * @param int $bucket
	 * @return void
	 */
	protected function _setMultiIncrementTransactionPart(Rediska_Transaction $transaction, $subject, $bucket) {
		$keyName = $this->_getKeyName($subject);

		$transaction->incrementinhash($keyName, $bucket, 1)
				->deletefromhash($keyName, ($bucket + 1) % $this->_bucketCount)
				->deletefromhash($keyName, ($bucket + 2) % $this->_bucketCount)
				->expire($keyName, $this->_subjectExpire);
	}

	/**
	 * return Rediska_Transaction instance
	 * @return Rediska_Transaction 
	 */
	protected function _getTransaction() {
		return $this->_rediska->transaction();
	}

	/**
	 * Adds the commands needed for the count function
	 *
	 * @param Rediska_Transaction $transaction
	 * @param string $subject A unique identifier, for example a session id or an IP
	 * @param int $bucket
	 * @param int $count
	 * @return void
	 */
	protected function _setMulitExecGetCountPart(Rediska_Transaction $transaction, $subject, $bucket, $count) {
		$keyName = $this->_getKeyName($subject);
		// Get the counts from the previous `$count` buckets
		$transaction->getfromhash($keyName, $bucket);
		for ($i = $count; $i > 0; $i--) {
			$transaction->getfromhash($keyName, (--$bucket + $this->_bucketCount) % $this->_bucketCount);
		}
		return $transaction;
	}

	/**
	 * get key name
	 * @param string $subject A unique identifier, for example a session id or an IP
	 * @return string 
	 */
	protected function _getKeyName($subject) {
		return $this->_action . ':' . $subject;
	}

}