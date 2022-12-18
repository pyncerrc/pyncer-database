<?php
namespace Pyncer\Database\Exception;

use Pyncer\Database\Exception\Exception;
use Pyncer\Exception\RuntimeException;

class DatabaseException extends RuntimeException implements
    Exception
{}
