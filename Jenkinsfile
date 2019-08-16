pipeline {
    agent any
    stages {
        stage('Three') {
            when {
                not {
                    branch "master"
                }
            }
            steps {
                echo "Hello not master"
            }
        }
        stage('Generate jar file'){
            when{
                branch "feature/*"
            }
            steps{
                echo "Hello universe try me"
            }
        }


    }
}