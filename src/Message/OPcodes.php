<?php

namespace Resolver\Message;

enum OPcodes: int {

	case QUERY    = 0;   // Query
	case INVERSE  = 1;   // Inverse Query
	case STATUS   = 2;   // Status
	case NOTIFY   = 4;   // Notify
	case UPDATE   = 5;   // Update

};
