'use client'

import React, { useEffect, useState } from 'react'
import styles from './preloader.module.css'

export default function Preloader() {
  const [loading, setLoading] = useState(true)

  useEffect(() => {
    const timer = setTimeout(() => {
      setLoading(false)
    }, 3000)

    return () => clearTimeout(timer)
  }, [])

  if (!loading) return null

  return (
    <div className={styles.preloader}>
      <div className={styles.logoContainer}>
        <img src="https://hebbkx1anhila5yf.public.blob.vercel-storage.com/majisticlogo-Gcp7uLSlL72ZGNUJTWNAeFXleCqaIr.png" alt="Majistic Logo" className={`${styles.logo} ${styles.responsiveLogo}`} />
        <div className={styles.glow}></div>
      </div>
      <div className={`${styles.loadingText} ${styles.responsiveText}`}>LOADING MAJISTIC</div>
    </div>
  )
}

