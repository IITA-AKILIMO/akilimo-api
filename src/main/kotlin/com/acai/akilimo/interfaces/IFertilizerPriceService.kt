package com.acai.akilimo.interfaces


import com.acai.akilimo.entities.FertilizerPrices

interface IFertilizerPriceService {
    fun fertilizers(): List<FertilizerPrices>

    fun saveFertilizerPrice(fertilizerPrices: FertilizerPrices): FertilizerPrices?

}
