package com.acai.akilimo

import org.springframework.boot.autoconfigure.SpringBootApplication
import org.springframework.boot.runApplication
import org.springframework.context.annotation.Bean
import org.springframework.web.client.RestTemplate



@SpringBootApplication
class AkilimoApplication
{
	@Bean
	fun getRestTemplate(): RestTemplate {
		return RestTemplate()
	}
}
fun main(args: Array<String>) {
	runApplication<AkilimoApplication>(*args)
}
