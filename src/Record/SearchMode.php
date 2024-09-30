<?php
namespace Pyncer\Database\Record;

enum SearchMode
{
    case BOOLEAN;
    case NATURAL_LANGUAGE;
    case QUERY_EXPANSION;
    case NATURAL_LANGUAGE_WITH_QUERY_EXPANSION;
}
