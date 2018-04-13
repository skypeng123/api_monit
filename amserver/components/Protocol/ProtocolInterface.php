<?php
namespace Amserver\Components\Protocol;


/**
 * Protocol interface
 */
interface ProtocolInterface
{

    /**
     * @param $recv_buffer
     * @return mixed
     */
    public static function decode($recv_buffer);

    /**
     * @param $data
     * @return mixed
     */
    public static function encode($data);
}
