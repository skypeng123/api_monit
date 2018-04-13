<?php
/**
 * Json协议
 *
 * @author    jip
 */
namespace Amserver\Components\Protocol;


class JsonProtocol implements ProtocolInterface
{

    /**
     * Encode.
     *
     * @param string $buffer
     * @return string
     */
    public static function encode($buffer)
    {
        return json_encode($buffer);
    }

    /**
     * Decode.
     *
     * @param string $buffer
     * @return string
     */
    public static function decode($buffer)
    {
        return json_decode($buffer,true);
    }
}
