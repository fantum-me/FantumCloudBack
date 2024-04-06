<?php

namespace App\Tests\Entity;

use App\Entity\Workspace;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class WorkspaceTest extends WebTestCase
{
    public function getEntity(): Workspace
    {
        return (new Workspace())->setName('Workspace');
    }

    public function assertHasErrors(Workspace $workspace, int $number): void
    {
        self::bootKernel();
        $errors = self::getContainer()->get('validator')->validate($workspace);
        $messages = [];
        foreach($errors as $error) {
            $messages[] = $error->getPropertyPath() . ' => ' . $error->getMessage();
        }
        $this->assertCount($number, $errors, implode(', ', $messages));
    }

    public function testValidEntity()
    {
        $this->assertHasErrors($this->getEntity(), 0);
    }

    public function testInvalidSuspiciousNameEntity()
    {
        $this->assertHasErrors($this->getEntity()->setName("suspicious: 8৪"), 1);
    }

    public function testInvalidShortNameEntity()
    {
        $this->assertHasErrors($this->getEntity()->setName("aa"), 1);
    }

    public function testInvalidLongNameEntity()
    {
        $this->assertHasErrors($this->getEntity()->setName(str_repeat("a", 32)), 1);
    }
}
