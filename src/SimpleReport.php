<?php

class SimpleReport extends AbstractReport
{
    /**
     * @return void
     */
    protected function _setData()
    {
        $this->_data = [
            'avg_post_length' => [],
            'max_post_length' => [],
            'total_posts' => [],
            'avg_posts' => [],
        ];
    }

    /**
     * @param array $data
     * @return void
     */
    protected function _calculateMetrics(array $data)
    {
        $counters = $this->_calculateCounters($data);

        foreach ($counters['months'] as $month) {
            $this->_data['avg_post_length'][$month] = round($counters['chars_per_month'][$month] / $counters['posts_per_month'][$month], 2);
            $this->_data['avg_posts'][$month]       = round($counters['posts_per_month'][$month] / $counters['users_per_month'][$month], 2);
        }

        $this->_data['max_post_length'] = $counters['max_chars_per_month'];
        $this->_data['total_posts']     = $counters['posts_per_week'];
    }

    /**
     * @param array $data
     * @return array
     */
    private function _calculateCounters(array $data) : array
    {
        $countUsersMonth = [];
        $countPostsMonth = [];
        $countLengthMonth = [];
        $countPostsWeek = [];
        $maxLengthMonth = [];
        foreach ($data as $post) {
            if (isset($maxLengthMonth[$post['month']])) {
                if ($maxLengthMonth[$post['month']] < $post['length']) {
                    $maxLengthMonth[$post['month']] = $post['length'];
                }
            } else {
                $maxLengthMonth[$post['month']] = $post['length'];
            }

            if (isset($countPostsWeek[$post['week']])) {
                $countPostsWeek[$post['week']] += 1;
            } else {
                $countPostsWeek[$post['week']] = 1;
            }

            if (isset($countPostsMonth[$post['month']])) {
                $countPostsMonth[$post['month']] += 1;
            } else {
                $countPostsMonth[$post['month']] = 1;
            }

            if (isset($countUsersMonth[$post['month']])) {
                $countUsersMonth[$post['month']][] = $post['user_id'];
            } else {
                $countUsersMonth[$post['month']] = [$post['user_id']];
            }

            if (isset($countLengthMonth[$post['month']])) {
                $countLengthMonth[$post['month']] += $post['length'];
            } else {
                $countLengthMonth[$post['month']] = $post['length'];
            }
        }

        foreach ($countUsersMonth as $month => $users) {
            $countUsersMonth[$month] = count(array_unique($users));
        }

        return [
          'months'              => array_keys($countPostsMonth),
          'posts_per_month'     => $countPostsMonth,
          'posts_per_week'      => $countPostsWeek,
          'users_per_month'     => $countUsersMonth,
          'chars_per_month'     => $countLengthMonth,
          'max_chars_per_month' => $maxLengthMonth,
        ];
    }
}