<?php
namespace App\Enums;

enum SystemActionType: string
{
    case LOGIN = 'login';
    case LOGOUT = 'logout';
    case CREATE_ACCOUNT = 'create_new_acc';
    case SALE_PROCESS = 'sale_process';
    case ADD_EXPENSE = 'add_expense';
    case ADD_EXPENSE_DRAFT = 'add_expense_draft';
    case ADD_DEPOSIT = 'add_deposit';
    case ADD_NEW_CLIENT = 'add_new_client';
    case ADD_NEW_PRODUCT = 'add_new_product';
    case ADD_QTY_OLD_PRODUCT = 'add_qty_old_product';
    case EDIT_PRODUCT = 'edit_product';
    case ADD_IMPORTANT_PRODUCT = 'add_important_product';
    case DELETE_IMPORTANT_PRODUCT = 'delete_important_product';
    case ADD_NEW_EXPENSE_TYPE = 'add_new_expense_type';
    case START_SHIFT = 'start_shift';
    case END_SHIFT = 'end_shift';
    case READ_DAILY_SHIFTS = 'read_daily_shifts';
}
