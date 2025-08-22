package config

var (
	// Threshold for Multimodal
	// Assuming scores are out of 10
	multimodalThreshold = 0.15
	likeThreshold       = 7
)

func GetMultimodalThreshold() float64 {
	return multimodalThreshold
}

func SetMultimodalThreshold(threshold float64) {
	multimodalThreshold = threshold
}

func GetLikeThreshold() int {
	return likeThreshold
}

func SetLikeThreshold(threshold int) {
	likeThreshold = threshold
}
