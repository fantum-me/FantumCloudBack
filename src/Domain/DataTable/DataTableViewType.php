<?php

namespace App\Domain\DataTable;

enum DataTableViewType: string {
    case TableView = 'table';
    case CalendarView = 'calendar';
    case BoardView = 'board';
}
