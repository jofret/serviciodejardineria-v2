<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BankAccount extends Model
{
    protected $fillable = [
        'name',
        'bank_name',
        'account_holder',
        'cbu',
        'cbu_alias',
    ];

    public function formattedDetails(): string
    {
        $lines = [
            "🏦 {$this->name}",
            "Banco: {$this->bank_name}",
            "Titular: {$this->account_holder}",
            "CBU: {$this->cbu}",
        ];

        if (filled($this->cbu_alias)) {
            $lines[] = "Alias: {$this->cbu_alias}";
        }

        return implode("\n", $lines);
    }
}
