<?php

namespace Tests\Unit\Services;

use App\Enums\Nature;
use App\Services\StatCalculatorService;
use PHPUnit\Framework\TestCase;

class StatCalculatorServiceTest extends TestCase
{
    private StatCalculatorService $service;

    protected function setUp(): void
    {
        $this->service = new StatCalculatorService();
    }

    /**
     * HP実数値計算テスト
     * 例: カイリューのHP (base:91, iv:31, ev:0, lv:50)
     * = floor((91*2 + 31 + floor(0/4)) * 50/100) + 50 + 10
     * = floor(213 * 0.5) + 60 = floor(106.5) + 60 = 106 + 60 = 166
     */
    public function test_calcHp_with_zero_ev(): void
    {
        $result = $this->service->calcHp(91, 31, 0, 50);
        $this->assertSame(166, $result);
    }

    /**
     * HP実数値 with 252EV
     * = floor((91*2 + 31 + floor(252/4)) * 50/100) + 50 + 10
     * = floor((182 + 31 + 63) * 50/100) + 60
     * = floor(276 * 0.5) + 60 = 138 + 60 = 198
     */
    public function test_calcHp_with_max_ev(): void
    {
        $result = $this->service->calcHp(91, 31, 252, 50);
        $this->assertSame(198, $result);
    }

    /**
     * HP実数値 at level 100
     * = floor((91*2 + 31 + 63) * 100/100) + 100 + 10
     * = floor(276) + 110 = 276 + 110 = 386
     */
    public function test_calcHp_at_level100(): void
    {
        $result = $this->service->calcHp(91, 31, 252, 100);
        $this->assertSame(386, $result);
    }

    /**
     * ステータス実数値（無補正）テスト
     * 例: ガブリアスの攻撃 (base:130, iv:31, ev:0, lv:50, nature:hardy=無補正)
     * = floor(floor((130*2 + 31 + 0) * 50/100) + 5) * 1.0
     * = floor(floor(291 * 0.5) + 5) = floor(145 + 5) = 150
     */
    public function test_calcStat_neutral_nature(): void
    {
        $result = $this->service->calcStat(130, 31, 0, 50, Nature::hardy, 'attack');
        $this->assertSame(150, $result);
    }

    /**
     * ステータス実数値（攻撃↑性格）テスト
     * 例: ガブリアスのようき(speed↑)の攻撃 → 攻撃は無補正
     * 例: アダマント（攻撃↑）の攻撃
     * = floor(150 * 1.1) = floor(165) = 165
     */
    public function test_calcStat_boosted_nature(): void
    {
        // adamant: attack+, sp_attack-
        $result = $this->service->calcStat(130, 31, 0, 50, Nature::adamant, 'attack');
        $this->assertSame(165, $result);
    }

    /**
     * ステータス実数値（攻撃↓性格）テスト
     * = floor(150 * 0.9) = floor(135) = 135
     */
    public function test_calcStat_reduced_nature(): void
    {
        // modest: sp_attack+, attack-
        $result = $this->service->calcStat(130, 31, 0, 50, Nature::modest, 'attack');
        $this->assertSame(135, $result);
    }

    /**
     * 252EV補正あり
     * = floor(floor((130*2 + 31 + 63) * 50/100) + 5)
     * = floor(floor(354 * 0.5) + 5)
     * = floor(177 + 5) = 182
     */
    public function test_calcStat_with_max_ev(): void
    {
        $result = $this->service->calcStat(130, 31, 252, 50, Nature::hardy, 'attack');
        $this->assertSame(182, $result);
    }

    /**
     * 低種族値・最低条件 (iv=0, ev=0, lv=1)
     */
    public function test_calcStat_minimum(): void
    {
        $result = $this->service->calcStat(10, 0, 0, 1, Nature::hardy, 'attack');
        // floor(floor((20+0+0)*1/100) + 5) = floor(0 + 5) = 5
        $this->assertSame(5, $result);
    }

    /**
     * HポケモンのHP (base:255 = マシェード等)
     * iv=31, ev=252, lv=50
     * = floor((255*2 + 31 + 63) * 50/100) + 60
     * = floor(604 * 0.5) + 60 = 302 + 60 = 362
     */
    public function test_calcHp_high_base(): void
    {
        $result = $this->service->calcHp(255, 31, 252, 50);
        $this->assertSame(362, $result);
    }
}
