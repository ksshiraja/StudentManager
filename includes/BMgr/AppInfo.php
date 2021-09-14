<?php 
    namespace BMgr;

    class AppInfo {
        public function compare($current, $other) {
            $current = $this->convert($current);
            $other   = $this->convert($other);
            if ($current > $other)
                return 1;
            if ($other > $current)
                return 2;
            if ($other == $current)
                return 0;
            return -1;
        }

        private function convert($v) {
            $v = explode('.', $v);
            return ((int) @$v[0]) * 1000000 + ((int)@$v[1]??0) * 1000 + ((int)@$v[2]??0);
        }
    }
?>