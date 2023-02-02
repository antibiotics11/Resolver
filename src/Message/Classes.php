<?php

namespace Resolver\Message;

enum Classes: int {

	case RESERVED  = 0;   // Reserved
	case INTERNET  = 1;   // Internet (IN)
	case CHAOS     = 3;   // Chaos (CH)
	case HESIOD    = 4;   // Hesiod (HS)

};
