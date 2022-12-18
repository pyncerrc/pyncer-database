<?php
namespace Pyncer\Database\Table;

enum ReferentialAction
{
    case CASCADE;
    case NO_ACTION;
    case RESTRICT;
    case SET_DEFAULT;
    case SET_NULL;
}
