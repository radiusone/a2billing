<?php

namespace A2billing;

class Customer extends User
{
    public const ACX_ACCESS = 1;
    public const ACX_PASSWORD = 2;
    public const ACX_SIP_IAX = 4;
    public const ACX_CALL_HISTORY = 8;
    public const ACX_PAYMENT_HISTORY = 16;
    public const ACX_VOUCHER = 32;
    public const ACX_INVOICES = 64;
    public const ACX_DID = 128;
    public const ACX_SPEED_DIAL = 256;
    public const ACX_RATECARD = 512;
    public const ACX_SIMULATOR = 1024;
    public const ACX_CALL_BACK = 2048;
    public const ACX_WEB_PHONE = 4096;
    public const ACX_CALLER_ID = 8192;
    public const ACX_SUPPORT = 16384;
    public const ACX_NOTIFICATION = 32768;
    public const ACX_AUTODIALER = 65536;
    public const ACX_PERSONALINFO = 131072;
    public const ACX_SEERECORDING = 262144;
}