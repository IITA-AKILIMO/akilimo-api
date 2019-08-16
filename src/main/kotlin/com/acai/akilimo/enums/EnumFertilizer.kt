package com.acai.akilimo.enums

enum class EnumFertilizer(vararg customFertilizerKeys: String) {
    UREA,
    CAN,
    SSP,
    MOP,
    DAP,
    TSP,
    NAFAKAPLUS,
    NPK17_17_17,
    NPK15_15_15,
    NPK20_10_10,
    CUSTOM_FERT_ONE("YARAMILA_UNIK"),
    CUSTOM_FERT_TWO,
    CUSTOM_FERT_THREE,
    CUSTOM_FERT_FOUR,
    CUSTOM_FERT_FIVE,
    CUSTOM_FERT_SIX,
    CUSTOM_FERT_SEVEN,
    CUSTOM_FERT_EIGHT;

    @Suppress("UNCHECKED_CAST")
    val fertilizerKey: Array<String> = customFertilizerKeys as Array<String>
}