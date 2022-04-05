package com.iita.akilimo.enums

enum class EnumFertilizer(vararg customFertilizerKeys: String) {
    UREA,
    CAN,
    SSP,
    MOP,
    DAP,
    TSP,
    NPK_17_17_17,
    NPK_15_15_15,
    NPK_20_10_10,
    NPK_20_12_26,
    NPK_20_12_16,
    NPK_11_22_21,
    NPK_25_10_10,
    NPK_15_20_20,
    NPK_23_10_5,
    NPK_12_30_17,
    YARAMILA_UNIK,
    CUSTOM_FERT_ONE,
    FOM_II_MBUR,
    FOM_I_BAGARA,
    FOM_I_TOTA_HAZA,
    DOLOMITE,
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
