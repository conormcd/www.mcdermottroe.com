<?php

/**
 * A simple fake Klein object to allow us to test #dispatch.
 *
 * @author Conor McDermottroe <conor@mcdermottroe.com>
 */
class FakeKlein
extends \Klein\Klein
{
    /**
     * A dummy dispatch which just returns its arguments.
     *
     * @param \Klein\Request  $request       See \Klein\Klein#dispatch.
     * @param \Klein\Response $response      See \Klein\Klein#dispatch.
     * @param boolean         $send_response See \Klein\Klein#dispatch.
     * @param int             $capture       See \Klein\Klein#dispatch.
     *
     * @return The arguments that are passed to it.
     */
    public function dispatch(
        \Klein\Request $request = null,
        \Klein\Response $response = null,
        $send_response = true,
        $capture = \Klein\Klein::DISPATCH_NO_CAPTURE
    ) {
        assert($request === null || $request instanceof \Klein\Request);
        assert($response === null || $response instanceof \Klein\Response);
        assert(is_bool($send_response));
        assert(is_int($capture));
        return func_get_args();
    }

    /**
     * Make routes() public so that they can be inspected for testing.
     *
     * @return array See \Klein\Klein->routes().
     */
    public function routes() {
        return $this->routes;
    }
}

?>
