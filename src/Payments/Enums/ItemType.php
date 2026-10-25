<?php

namespace A2billing\Payments\Enums;

enum ItemType: string
{
    case Calls = "CALLS";
    case Charge = "CHARGE";
    case Did = "DID";
    case Postpaid = "POSTPAID";
    case Subscription = "SUBSCR";
    case Other = "";
}
