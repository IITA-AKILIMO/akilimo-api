pipeline {
    agent any
    stages {
        stage('One') {
            steps {
                echo 'Hi, this is Sammy from masgeek'
            }
        }
        stage('Two') {
            steps {
                input('Do you want to proceed?')
            }
        }
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
            stage('Publish') {
              when {
                branch 'master'
              }
              steps {
                withDockerRegistry([ credentialsId: "6544de7e-17a4-4576-9b9b-e86bc1e4f903", url: "" ]) {
                  sh 'docker push brightbox/terraform:latest'
                  sh 'docker push brightbox/cli:latest'
                }
              }
        stage('Four') {
            parallel {
                stage('Unit Test') {
                    steps {
                        echo "Running the unit test..."
                    }
                }
                stage('Integration test') {
                    agent {
                        docker {
                            reuseNode true
                            image 'ubuntu'
                        }
                    }
                    steps {
                        echo "Running the integration test..."
                    }
                }
            }
        }
    }
}