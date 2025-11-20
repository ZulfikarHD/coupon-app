<?php

namespace Tests\Unit;

use App\Helpers\PhoneHelper;
use Tests\TestCase;

class PhoneHelperTest extends TestCase
{
    public function test_normalize_converts_08xx_format_to_international()
    {
        $result = PhoneHelper::normalize('081234567890');
        
        $this->assertEquals('6281234567890', $result);
    }

    public function test_normalize_converts_8xx_format_to_international()
    {
        $result = PhoneHelper::normalize('81234567890');
        
        $this->assertEquals('6281234567890', $result);
    }

    public function test_normalize_keeps_already_normalized_number()
    {
        $result = PhoneHelper::normalize('6281234567890');
        
        $this->assertEquals('6281234567890', $result);
    }

    public function test_normalize_removes_plus_sign()
    {
        $result = PhoneHelper::normalize('+6281234567890');
        
        $this->assertEquals('6281234567890', $result);
    }

    public function test_normalize_removes_non_numeric_characters()
    {
        $result = PhoneHelper::normalize('0812-3456-7890');
        
        $this->assertEquals('6281234567890', $result);
    }

    public function test_normalize_handles_spaces()
    {
        $result = PhoneHelper::normalize('0812 3456 7890');
        
        $this->assertEquals('6281234567890', $result);
    }

    public function test_normalize_handles_parentheses()
    {
        $result = PhoneHelper::normalize('(0812) 3456-7890');
        
        $this->assertEquals('6281234567890', $result);
    }

    public function test_format_for_display_converts_international_to_local()
    {
        $result = PhoneHelper::formatForDisplay('6281234567890');
        
        $this->assertStringStartsWith('0812', $result);
        $this->assertStringContainsString('-', $result);
    }

    public function test_format_for_display_formats_correctly()
    {
        $result = PhoneHelper::formatForDisplay('6281234567890');
        
        $this->assertEquals('0812-3456-7890', $result);
    }

    public function test_format_for_display_handles_already_local_format()
    {
        $result = PhoneHelper::formatForDisplay('081234567890');
        
        $this->assertEquals('0812-3456-7890', $result);
    }

    public function test_format_for_display_handles_short_numbers()
    {
        $result = PhoneHelper::formatForDisplay('12345');
        
        $this->assertEquals('12345', $result);
    }

    public function test_normalize_for_search_normalizes_phone()
    {
        $result = PhoneHelper::normalizeForSearch('081234567890');
        
        $this->assertEquals('6281234567890', $result);
    }

    public function test_normalize_for_search_handles_partial_numbers()
    {
        $result = PhoneHelper::normalizeForSearch('812345');
        
        $this->assertEquals('62812345', $result);
    }
}
