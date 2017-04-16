<?php

namespace Broadway;

class Bla_Class
{
    /**
     * @return string
     */
    public function bla_method($anotherVar) {
        if ($anotherVar < 5) {
            if ($anotherVar < 4) {
                if ($anotherVar < 2) {
                    if ($anotherVar < 1) {
                        $thirdVar = 9;
                    }
                }
            }
        } elseif ($anotherVar > 5) {
            $thirdVar = 11;
            if ($anotherVar > 6) {
                $thirdVar = 10;
            }
        } else {
            $thirdVar = 12;
        }

        return $thirdVar;
    }
}
