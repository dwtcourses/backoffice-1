<?= "<?php\n" ?>

declare(strict_types=1);

namespace <?= $namespace; ?>;

use App\BasicRum\CoreObjects\ReaderHintInterface;

class ReaderHint implements ReaderHintInterface
{
    public function getFieldName(): string
    {
        return '<?= $field_name; ?>';
    }

    public function getTabledName(): string
    {
        return '<?= $table_name; ?>';
    }
}
