<?php

namespace Light\UI\Framework\Persist;
use Light\Exception;

class StoreManager
{
	/**
	 * A list of active persistent stores;
	 */
	private $stores = array();
	
	const STATE_NONE		= 0;
	const STATE_OPEN		= 1;
	const STATE_CLICLOSED	= 2;
	const STATE_CLOSED		= 3;
	
	private $state = self::STATE_NONE;
	
	public function __construct()
	{
		$this->stores[Store::REQUEST] = new UriStoreImpl();
		$this->stores[Store::REQUEST + Store::SERVER] = new RequestSessionStoreImpl();
	}
	
	public function open()
	{
		if ($this->state != self::STATE_NONE)
			throw new Exception\Exception("Invalid state: %1", $this->state);
		foreach($this->stores as $store) $store->open();
		$this->state = self::STATE_OPEN;
	}
	
	public function closeClientStores()
	{
		if ($this->state != self::STATE_OPEN)
			throw new Exception\Exception("Invalid state: %1", $this->state);
		
		foreach($this->stores as $store)
		{
			if ($store instanceof ClientStore) $store->close();	
		}
		
		$this->state = self::STATE_CLICLOSED;
	}
	
	public function closeServerStores()
	{
		if ($this->state != self::STATE_CLICLOSED)
			throw new Exception\Exception("Invalid state: %1", $this->state);

		foreach($this->stores as $store)
		{
			if (!($store instanceof ClientStore)) $store->close();	
		}
		
		$this->state = self::STATE_CLOSED;
	}
	
	/**
	 * Returns a Store meeting the requirements of the specified property.
	 * @param Property $property
	 */
	public function getStoreFor(Property $property)
	{
		$scope = $property->getScope();
		$using = $property->getUsing();
		if (is_null($using))
		{
			$using = Store::URI;
		}
				
		return $this->getStore($scope + $using);
	}
	
	public function getStore($type)
	{
		if (isset($this->stores[$type]))
		{
			return $this->stores[$type];
		}
		
		if (($type & Store::SESSION) != 0)
		{
			$type = Store::SESSION;
		}
		else if (($type & Store::REQUEST) != 0)
		{
			$type = Store::REQUEST;
		}
		else
		{
			throw new \Exception("Invalid persistent store type.");
		}

		return $this->stores[$type];
	}
	
	public function setStore($type, Store $store)
	{
		$this->stores[$type] = $store;
		return $this;
	}
	
}