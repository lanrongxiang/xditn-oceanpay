<?php

namespace Xditn\Oceanpay\Contracts;

use Illuminate\Http\Request;
use Xditn\Oceanpay\Providers\Account;
use Xditn\Oceanpay\Providers\Deposit;
use Xditn\Oceanpay\Providers\Withdrawal;

interface Provider
{
    public function setConfig(?array $config): static;

    public function initiateDeposit(array $parameters = []): Deposit;

    public function fetchDeposit(array $parameters = []): Deposit;

    public function initiateWithdrawal(array $parameters = []): Withdrawal;

    public function fetchWithdrawal(array $parameters = []): Withdrawal;

    public function fetchBalance(): Account;

    public function mapDepositToObject(array $data): Deposit;

    public function mapWithdrawalToObject(array $data): Withdrawal;

    public function isValidAuthorization(Request $request);

    public function successfulResponse();

    public function failedResponse();
}
