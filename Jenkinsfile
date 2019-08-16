pipeline {
    agent any
    environment {
        registry = 'iita/acai-akilimo-api'
        registryCredential = 'dockerhub'
        dockerImage = ''
    }
    stages {
        stage('Chmod permissions') {
            steps {
                echo "Chmod permissions"
                sh 'chmod +x ./gradlew'
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
        stage('Clean code base') {
            steps {
                echo "Cleaning project"
                sh './gradlew clean'
            }
        }

        stage('Run tests and check coverage') {
            steps {
                echo "Running tests"
                sh './gradlew check'
            }
        }
        stage('Build binary files for release branches') {
            when {
                not {
                    anyOf {
                        branch 'master';
                        branch 'develop'
                    }
                }
            }
            steps {
                echo "Running tests"
                sh './gradlew build assemble'
            }
        }
        stage('Build latest') {
            when {
                not {
                    anyOf {
                        branch 'master';
                        branch 'develop'
                    }
                }
            }
            steps {
//                echo "Building docker image"
//                sh "docker build -f Dockerfile -t iita/acai-akilimo-api:${BUILD_NUMBER} ."
                script {
                    dockerImage = docker.build registry + ":${BUILD_NUMBER}"
                }
            }
        }

        stage('Archive artifacts') {
            when {
                not {
                    anyOf {
                        branch "master";
                        branch "develop"
                    }
                }
            }
            steps {
                archiveArtifacts artifacts: 'build/libs/akilimo*.jar'
            }
        }
        stage('Push image') {
            steps {
                script {
                    withCredentials([usernamePassword(credentialsId: 'dockerhub', usernameVariable: 'USERNAME', passwordVariable: 'PASSWORD')]) {
                        docker.withRegistry('', 'dockerhub') {
                            sh "docker login -u ${USERNAME} -p ${PASSWORD}"
                            myImage.push("${env.BUILD_NUMBER}")
                            myImage.push("latest")
                        }
                    }
                }
            }
        }

//        stage('Deploy Image') {
//            steps {
//                script {
//                    docker.withRegistry('', registryCredential) {
//                        dockerImage.push()
//                    }
//                }
//            }
//        }

//        stage('Remove Unused docker image') {
//            steps {
//                sh "docker rmi $registry:$BUILD_NUMBER"
//            }
//        }

    }
}