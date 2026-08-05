<?php

namespace Tests\Feature;

use App\Http\Controllers\ManpowerPrintController;
use Tests\TestCase;

class ManpowerPrintControllerTest extends TestCase
{
    public function test_build_level_key_uses_database_fk_names(): void
    {
        $controller = new ManpowerPrintController();
        $method = new \ReflectionMethod($controller, 'buildLevelKey');
        $method->setAccessible(true);

        $this->assertSame('employee_id|42|7', $method->invoke($controller, 'employee_id', 42, 7));
        $this->assertSame('intern_id|11|3', $method->invoke($controller, 'intern_id', 11, 3));
    }
}
