<?php
declare(strict_types=1);
namespace Kraut\Model;

interface UserInterface
{
    /**
     * Get the roles of the user.
     *
     * @return string[] An array of roles.
     */
    public function getRoles(): array;
}
?>