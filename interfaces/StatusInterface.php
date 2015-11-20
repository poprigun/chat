<?php

namespace poprigun\chat\interfaces;

interface StatusInterface{

    const STATUS_PENDING    = 0;
    const STATUS_MODERATION = 3;
    const STATUS_BLOCKED    = 6;
    const STATUS_IGNORE     = 9;
    const STATUS_DELETED    = 12;
    const STATUS_ACTIVE     = 30;
}