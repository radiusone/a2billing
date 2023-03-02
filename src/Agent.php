<?php

namespace A2billing;

class Agent extends User
{
    public const ACX_ACCESS = 1;
    public const ACX_CUSTOMER = 1;
    public const ACX_BILLING = 2;
    public const ACX_RATECARD = 4;
    public const ACX_CALL_REPORT = 8;
    public const ACX_MYACCOUNT = 16;
    public const ACX_SUPPORT = 32;
    public const ACX_CREATE_CUSTOMER = 64;
    public const ACX_EDIT_CUSTOMER = 128;
    public const ACX_DELETE_CUSTOMER = 256;
    public const ACX_GENERATE_CUSTOMER = 512;
    public const ACX_SIGNUP = 1024;
    public const ACX_VOIPCONF = 2048;
    public const ACX_SEE_CUSTOMERS_CALLERID = 4096;

}