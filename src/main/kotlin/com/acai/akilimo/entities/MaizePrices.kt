package com.acai.akilimo.entities

import lombok.Data
import org.hibernate.annotations.CreationTimestamp
import org.hibernate.annotations.GenericGenerator
import org.hibernate.annotations.UpdateTimestamp
import java.time.LocalDateTime
import javax.persistence.*

@Entity
@Data
@Table(name = "maize_prices")
class MaizePrices : ProducePrice()