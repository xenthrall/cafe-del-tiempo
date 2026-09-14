<?php

namespace Tequia\Vault\Enums;

enum VaultItemType: string
{
    case Password = 'password';
    case Note = 'note';
    case RecoveryCode = 'recovery_code';
}
