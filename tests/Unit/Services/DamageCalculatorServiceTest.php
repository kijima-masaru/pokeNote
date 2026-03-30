<?php

namespace Tests\Unit\Services;

use App\Models\Move;
use App\Services\DamageCalculatorService;
use PHPUnit\Framework\TestCase;

class DamageCalculatorServiceTest extends TestCase
{
    private DamageCalculatorService $service;

    // 基準テスト値
    // lv=50, atk=150, def=100, power=80
    // baseDamage = floor(floor(22 * 80 * 150/100) / 50) + 2 = 54
    private const BASE_ATK_STATS = ['attack' => 150, 'sp_attack' => 100, 'hp' => 200];
    private const BASE_DEF_STATS = ['defense' => 100, 'sp_defense' => 80, 'hp' => 300];
    private const BASE_LEVEL     = 50;

    protected function setUp(): void
    {
        $this->service = new DamageCalculatorService();
    }

    /**
     * Move のモックを生成（DB不要）
     * Eloquent の __get は getAttribute() を呼ぶため、それをスタブする
     */
    private function makeMove(string $category, ?int $power, string $type): Move
    {
        $move = $this->getMockBuilder(Move::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getAttribute'])
            ->getMock();

        $move->method('getAttribute')->will($this->returnValueMap([
            ['category', $category],
            ['power',    $power],
            ['type',     $type],
        ]));

        return $move;
    }

    // ──────────────────────────────────────────────
    // ステータス技・無威力技は 0 を返す
    // ──────────────────────────────────────────────

    public function test_status_move_returns_zero_damage(): void
    {
        $move = $this->makeMove('status', null, 'normal');
        $result = $this->service->calculateFromRaw(
            self::BASE_ATK_STATS, ['normal'], self::BASE_LEVEL,
            self::BASE_DEF_STATS, ['normal'], $move
        );

        $this->assertSame(0, $result['damage_min']);
        $this->assertSame(0, $result['damage_max']);
        $this->assertSame(false, $result['one_shot']);
        $this->assertCount(16, $result['rolls']);
        $this->assertSame(0, $result['rolls'][0]);
    }

    // ──────────────────────────────────────────────
    // 基本ダメージ計算（物理・補正なし）
    // ──────────────────────────────────────────────

    public function test_basic_physical_damage(): void
    {
        // fire move vs normal defender: 相性1.0, STABなし
        $move   = $this->makeMove('physical', 80, 'fire');
        $result = $this->service->calculateFromRaw(
            self::BASE_ATK_STATS, ['normal'], self::BASE_LEVEL,
            self::BASE_DEF_STATS, ['normal'], $move
        );

        // baseDamage = floor(floor(22 * 80 * 150/100) / 50) + 2 = 54
        // rolls[0]  = floor(54 * 85/100) = 45
        // rolls[15] = floor(54 * 100/100) = 54
        $this->assertSame(45, $result['damage_min']);
        $this->assertSame(54, $result['damage_max']);
        $this->assertCount(16, $result['rolls']);
        $this->assertSame(45, $result['rolls'][0]);
        $this->assertSame(54, $result['rolls'][15]);
    }

    public function test_basic_special_damage(): void
    {
        // 特殊わざ: sp_attack=100 を使用
        // baseDamage = floor(floor(22 * 80 * 100/100) / 50) + 2 = floor(1760/50) + 2 = 35 + 2 = 37
        // rolls[0] = floor(37 * 85/100) = 31, rolls[15] = 37
        $move   = $this->makeMove('special', 80, 'water');
        $result = $this->service->calculateFromRaw(
            self::BASE_ATK_STATS, ['normal'], self::BASE_LEVEL,
            self::BASE_DEF_STATS, ['normal'], $move
        );

        $this->assertSame(31, $result['damage_min']);
        $this->assertSame(37, $result['damage_max']);
    }

    // ──────────────────────────────────────────────
    // STAB（タイプ一致）補正
    // ──────────────────────────────────────────────

    public function test_stab_bonus_increases_damage(): void
    {
        // fire move vs normal, attacker is fire type → STAB 1.5x
        // baseDamage pre-STAB = 54, after = floor(54 * 1.5) = 81
        // rolls[15] = 81
        $move   = $this->makeMove('physical', 80, 'fire');
        $result = $this->service->calculateFromRaw(
            self::BASE_ATK_STATS, ['fire'], self::BASE_LEVEL,
            self::BASE_DEF_STATS, ['normal'], $move
        );

        $this->assertSame(81, $result['damage_max']);
        // rolls[0] = floor(81 * 85/100) = floor(68.85) = 68
        $this->assertSame(68, $result['damage_min']);
    }

    // ──────────────────────────────────────────────
    // タイプ相性
    // ──────────────────────────────────────────────

    public function test_super_effective_doubles_damage(): void
    {
        // fire vs grass = 2x
        // baseDamage = 54, after type = floor(54 * 2) = 108
        // rolls[0] = floor(108 * 85/100) = 91, rolls[15] = 108
        $move   = $this->makeMove('physical', 80, 'fire');
        $result = $this->service->calculateFromRaw(
            self::BASE_ATK_STATS, ['normal'], self::BASE_LEVEL,
            self::BASE_DEF_STATS, ['grass'], $move
        );

        $this->assertSame(2.0, $result['type_effectiveness']);
        $this->assertSame(91, $result['damage_min']);
        $this->assertSame(108, $result['damage_max']);
    }

    public function test_not_very_effective_halves_damage(): void
    {
        // fire vs fire = 0.5x
        // floor(54 * 0.5) = 27
        // rolls[0] = floor(27 * 85/100) = 22, rolls[15] = 27
        $move   = $this->makeMove('physical', 80, 'fire');
        $result = $this->service->calculateFromRaw(
            self::BASE_ATK_STATS, ['normal'], self::BASE_LEVEL,
            self::BASE_DEF_STATS, ['fire'], $move
        );

        $this->assertSame(0.5, $result['type_effectiveness']);
        $this->assertSame(22, $result['damage_min']);
        $this->assertSame(27, $result['damage_max']);
    }

    public function test_immune_type_returns_zero_damage(): void
    {
        // normal vs ghost = 0x
        $move   = $this->makeMove('physical', 80, 'normal');
        $result = $this->service->calculateFromRaw(
            self::BASE_ATK_STATS, ['normal'], self::BASE_LEVEL,
            self::BASE_DEF_STATS, ['ghost'], $move
        );

        $this->assertSame(0.0, $result['type_effectiveness']);
        $this->assertSame(0, $result['damage_min']);
        $this->assertSame(0, $result['damage_max']);
        $this->assertSame(array_fill(0, 16, 0), $result['rolls']);
    }

    // ──────────────────────────────────────────────
    // 天候補正
    // ──────────────────────────────────────────────

    public function test_sunny_weather_boosts_fire_move(): void
    {
        // sunny + fire → 1.5x
        // floor(54 * 1.5) = 81, rolls[15] = 81
        $move   = $this->makeMove('physical', 80, 'fire');
        $result = $this->service->calculateFromRaw(
            self::BASE_ATK_STATS, ['normal'], self::BASE_LEVEL,
            self::BASE_DEF_STATS, ['normal'], $move,
            [], [], 'sunny'
        );

        $this->assertSame(81, $result['damage_max']);
    }

    public function test_sunny_weather_weakens_water_move(): void
    {
        // sunny + water → 0.5x
        // baseDamage (special, sp_atk=100) = 37, floor(37 * 0.5) = 18
        // rolls[15] = 18
        $move   = $this->makeMove('special', 80, 'water');
        $result = $this->service->calculateFromRaw(
            self::BASE_ATK_STATS, ['normal'], self::BASE_LEVEL,
            self::BASE_DEF_STATS, ['normal'], $move,
            [], [], 'sunny'
        );

        $this->assertSame(18, $result['damage_max']);
    }

    public function test_rainy_weather_boosts_water_move(): void
    {
        // rainy + water → 1.5x
        // base = 37, floor(37 * 1.5) = 55
        $move   = $this->makeMove('special', 80, 'water');
        $result = $this->service->calculateFromRaw(
            self::BASE_ATK_STATS, ['normal'], self::BASE_LEVEL,
            self::BASE_DEF_STATS, ['normal'], $move,
            [], [], 'rainy'
        );

        $this->assertSame(55, $result['damage_max']);
    }

    // ──────────────────────────────────────────────
    // フィールド補正
    // ──────────────────────────────────────────────

    public function test_grassy_terrain_boosts_grass_move(): void
    {
        // grassy + grass → 1.3x (非ひこうタイプ)
        // baseDamage (physical, atk=150) for grass move vs normal = 54
        // floor(54 * 1.3) = floor(70.2) = 70
        $move   = $this->makeMove('physical', 80, 'grass');
        $result = $this->service->calculateFromRaw(
            self::BASE_ATK_STATS, ['normal'], self::BASE_LEVEL,
            self::BASE_DEF_STATS, ['normal'], $move,
            [], [], 'none', 'grassy'
        );

        $this->assertSame(70, $result['damage_max']);
    }

    public function test_grassy_terrain_does_not_apply_for_flying_type(): void
    {
        // ひこうタイプのアタッカーはフィールド補正なし
        // baseDamage = 54 (補正なし)
        $move   = $this->makeMove('physical', 80, 'grass');
        $result = $this->service->calculateFromRaw(
            self::BASE_ATK_STATS, ['flying'], self::BASE_LEVEL,
            self::BASE_DEF_STATS, ['normal'], $move,
            [], [], 'none', 'grassy'
        );

        $this->assertSame(54, $result['damage_max']);
    }

    // ──────────────────────────────────────────────
    // 急所（クリティカルヒット）
    // ──────────────────────────────────────────────

    public function test_critical_hit_multiplies_damage(): void
    {
        // 急所: 1.5x
        // floor(54 * 1.5) = 81
        $move   = $this->makeMove('physical', 80, 'fire');
        $result = $this->service->calculateFromRaw(
            self::BASE_ATK_STATS, ['normal'], self::BASE_LEVEL,
            self::BASE_DEF_STATS, ['normal'], $move,
            [], [], 'none', 'none', true
        );

        $this->assertSame(81, $result['damage_max']);
    }

    // ──────────────────────────────────────────────
    // やけど補正
    // ──────────────────────────────────────────────

    public function test_burn_halves_physical_damage(): void
    {
        // physical + burned → 0.5x
        // floor(54 * 0.5) = 27
        $move   = $this->makeMove('physical', 80, 'fire');
        $result = $this->service->calculateFromRaw(
            self::BASE_ATK_STATS, ['normal'], self::BASE_LEVEL,
            self::BASE_DEF_STATS, ['normal'], $move,
            [], [], 'none', 'none', false, ['burned']
        );

        $this->assertSame(27, $result['damage_max']);
    }

    public function test_burn_does_not_affect_special_damage(): void
    {
        // special + burned → 補正なし
        // rolls[15] = 37
        $move   = $this->makeMove('special', 80, 'water');
        $result = $this->service->calculateFromRaw(
            self::BASE_ATK_STATS, ['normal'], self::BASE_LEVEL,
            self::BASE_DEF_STATS, ['normal'], $move,
            [], [], 'none', 'none', false, ['burned']
        );

        $this->assertSame(37, $result['damage_max']);
    }

    // ──────────────────────────────────────────────
    // ランク補正
    // ──────────────────────────────────────────────

    public function test_positive_attack_rank_increases_damage(): void
    {
        // atk_rank = +1: multiplier = (2+1)/2 = 1.5
        // atk = floor(150 * 1.5) = 225
        // baseDamage = floor(floor(22 * 80 * 225/100) / 50) + 2 = floor(3960/50) + 2 = 79 + 2 = 81
        // rolls[15] = 81
        $move   = $this->makeMove('physical', 80, 'fire');
        $result = $this->service->calculateFromRaw(
            self::BASE_ATK_STATS, ['normal'], self::BASE_LEVEL,
            self::BASE_DEF_STATS, ['normal'], $move,
            ['attack' => 1], []
        );

        $this->assertSame(81, $result['damage_max']);
    }

    public function test_negative_defense_rank_increases_damage(): void
    {
        // def_rank = -1: multiplier = 2/(2-(-1)) = 2/3 ≒ 0.667
        // def = floor(100 * 2/3) = floor(66.67) = 66
        // baseDamage = floor(floor(22 * 80 * 150/66) / 50) + 2
        // = floor(floor(22 * 181.82) / 50) + 2
        // = floor(floor(4000) / 50) + 2  ← 正確に: 150/66 * 80 * 22 = floor(3999.99...)
        // Let me recalculate:
        // atk=150, def=floor(100 * 2/(2+1)) = floor(66.67) = 66
        // floor(22 * 80 * 150 / 66) = floor(22 * 12000/66) = floor(22 * 181.818...) = floor(3999.99...) = 3999
        // floor(3999/50) = floor(79.98) = 79
        // 79 + 2 = 81
        $move   = $this->makeMove('physical', 80, 'fire');
        $result = $this->service->calculateFromRaw(
            self::BASE_ATK_STATS, ['normal'], self::BASE_LEVEL,
            self::BASE_DEF_STATS, ['normal'], $move,
            [], ['defense' => -1]
        );

        $this->assertSame(81, $result['damage_max']);
    }

    // ──────────────────────────────────────────────
    // one_shot / two_shot 判定
    // ──────────────────────────────────────────────

    public function test_one_shot_when_min_damage_exceeds_hp(): void
    {
        // HP = 40 で最小ダメージ 45 以上 → one_shot
        $defStats = array_merge(self::BASE_DEF_STATS, ['hp' => 40]);
        $move     = $this->makeMove('physical', 80, 'fire');
        $result   = $this->service->calculateFromRaw(
            self::BASE_ATK_STATS, ['normal'], self::BASE_LEVEL,
            $defStats, ['normal'], $move
        );

        $this->assertTrue($result['one_shot']);
        $this->assertTrue($result['two_shot']);
    }

    public function test_two_shot_when_double_min_damage_exceeds_hp(): void
    {
        // HP = 80 (45*2=90 >= 80) → two_shot だが one_shot ではない
        $defStats = array_merge(self::BASE_DEF_STATS, ['hp' => 80]);
        $move     = $this->makeMove('physical', 80, 'fire');
        $result   = $this->service->calculateFromRaw(
            self::BASE_ATK_STATS, ['normal'], self::BASE_LEVEL,
            $defStats, ['normal'], $move
        );

        $this->assertFalse($result['one_shot']);
        $this->assertTrue($result['two_shot']);
    }

    // ──────────────────────────────────────────────
    // damage_percent 計算
    // ──────────────────────────────────────────────

    public function test_damage_percent_is_calculated_correctly(): void
    {
        // HP = 300, damage_min = 45, damage_max = 54
        // percent_min = round(45/300*100, 1) = 15.0
        // percent_max = round(54/300*100, 1) = 18.0
        $move   = $this->makeMove('physical', 80, 'fire');
        $result = $this->service->calculateFromRaw(
            self::BASE_ATK_STATS, ['normal'], self::BASE_LEVEL,
            self::BASE_DEF_STATS, ['normal'], $move
        );

        $this->assertSame(15.0, $result['damage_percent_min']);
        $this->assertSame(18.0, $result['damage_percent_max']);
    }

    // ──────────────────────────────────────────────
    // 複合補正
    // ──────────────────────────────────────────────

    public function test_stab_plus_super_effective(): void
    {
        // fire + STAB + super effective (fire vs grass = 2x)
        // baseDamage = 54
        // after STAB: floor(54 * 1.5) = 81
        // after type: floor(81 * 2) = 162
        // rolls[0] = floor(162 * 85/100) = floor(137.7) = 137
        // rolls[15] = 162
        $move   = $this->makeMove('physical', 80, 'fire');
        $result = $this->service->calculateFromRaw(
            self::BASE_ATK_STATS, ['fire'], self::BASE_LEVEL,
            self::BASE_DEF_STATS, ['grass'], $move
        );

        $this->assertSame(3.0, $result['type_effectiveness']);
        $this->assertSame(162, $result['damage_max']);
        $this->assertSame(137, $result['damage_min']);
    }

    // ──────────────────────────────────────────────
    // ランク補正計算の境界値
    // ──────────────────────────────────────────────

    public function test_rank_multiplier_max_positive(): void
    {
        // rank = +6: multiplier = (2+6)/2 = 4.0
        // atk = floor(150 * 4.0) = 600
        // baseDamage = floor(floor(22 * 80 * 600/100) / 50) + 2
        // = floor(floor(105600) / 50) + 2 = floor(2112) + 2 = 2114
        // rolls[15] = 2114
        $move   = $this->makeMove('physical', 80, 'fire');
        $result = $this->service->calculateFromRaw(
            self::BASE_ATK_STATS, ['normal'], self::BASE_LEVEL,
            self::BASE_DEF_STATS, ['normal'], $move,
            ['attack' => 6], []
        );

        $this->assertSame(2114, $result['damage_max']);
    }
}
