<?php

namespace Light\UI\Framework\Persist;

/**
 * An empty interface that identifies {@link Store}s that persist information on the client tier.
 * Any store that implements this interface will be closed before the rendering stage as its serialized
 * data needs to be available for output to the client.
 * @author Piotrek
 *
 */
interface ClientStore
{	
}